<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Dashboard extends Admin_Controller {
public $data;
 public $update_m;
 /*
| -----------------------------------------------------
| PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
| -----------------------------------------------------
| AUTHOR:			INILABS TEAM
| -----------------------------------------------------
| EMAIL:			info@inilabs.net
| -----------------------------------------------------
| COPYRIGHT:		RESERVED BY INILABS IT
| -----------------------------------------------------
| WEBSITE:			http://inilabs.net
| -----------------------------------------------------
*/

	protected $_versionCheckingUrl = 'http://demo.inilabs.net/autoupdate/update/index';

	function __construct() {
		parent::__construct();
		$this->load->model('systemadmin_m');
		$this->load->model("setting_m");
		$this->load->model("notice_m");
		$this->load->model("student_m");
		$this->load->model("classes_m");
		$this->load->model("teacher_m");
		$this->load->model("parents_m");
		$this->load->model("subject_m");
		$this->load->model('event_m');
		$this->load->model('laporan_bulanan');
		$this->load->model('question_group_m');
		$this->load->model('question_level_m');
		$this->load->model('question_bank_m');
		$this->load->model('online_exam_user_status_m');
		$this->load->model('online_exam_m');
		$this->load->model('studentgroup_m');
		$this->load->model('loginlog_m');

		$language = $this->session->userdata('lang');
		$this->lang->load('dashboard', $language);
	}

	public function index() {

		// dd(phpinfo());
		$this->data['headerassets'] = array(
			'js' => array(
				'assets/highcharts/highcharts.js',
				'assets/highcharts/highcharts-more.js',
				'assets/highcharts/data.js',
				'assets/highcharts/drilldown.js',
				'assets/highcharts/exporting.js'
			)
		);
		$list = [];
		$verif = [];
		$not_verif = [];
		$parents = $this->student_m->get_student(); // Ambil data parents

		foreach ($parents as $key => $value) {
			// Hitung jumlah laporan bulanan per parent
			$this->db->select('*'); // Replace '*' with the specific columns you need
			$this->db->from('laporan_bulanan'); // Replace 'users' with your table name
			$this->db->where('create_userID', $value->studentID);
			$this->db->where('is_verified', 1);
			$subquery = $this->db->get();
			$count = $subquery->num_rows();
			array_push($list, $value->name,  // Menggunakan nama parent
				// 'y' => $count,     // Jumlah laporan bulanan
				// 'drilldown' => $value->name // Drilldown berdasarkan nama parent
			);
			array_push($verif, $count);
			$this->db->select('*'); // Replace '*' with the specific columns you need
			$this->db->from('laporan_bulanan'); // Replace 'users' with your table name
			$this->db->where('create_userID', $value->studentID);
			$this->db->where('is_verified', 0);
			$n_subquery = $this->db->get();
			$count_n = $n_subquery->num_rows();
			array_push($not_verif, $count_n);
		}
		$jsonResult = json_encode($list, JSON_PRETTY_PRINT);

		$list_exam = [];
		$exam_ids = [];
		$online_ex = $this->online_exam_m->get_order_by_online_exam();
		foreach ($online_ex as $key => $exams) {
			array_push($list_exam, $exams->name);
			array_push($exam_ids, $exams->onlineExamID);
		}
		// dd($list_exam);
		// var_dump($exam_ids);
		$this->data['exam'] = $list_exam;
		$this->data['lapbul_report'] = $list;
		$this->data['verif'] = $verif;
		$this->data['not_verif'] = $not_verif;
		// print_r($list);

		// $results = $this->question_level_report_m->get_question_level_report();
		$responsible = [];
		$student = $this->student_m->get_student();
		foreach ($student as $key => $students) {
			$temp = [
				'name' => $students->name, 
				'data' => []
			];
		
			// Tambahkan nilai ke dalam score array
			foreach ($exam_ids as $exam) {
				
				$userExamCheck = $this->online_exam_user_status_m->get_order_by_online_exam_user_status([
                    'userID'       => $students->studentID,
                    'onlineExamID'    => $exam
                ]);
				
				// dd($userExamCheck);
				if (inicompute($userExamCheck) > 0){
					// var_dump($userExamCheck->score_verifikasi);
					$temp['data'][] = isset($userExamCheck[0]->score_verifikasi) ? (int)$userExamCheck[0]->score_verifikasi : 0;
				}
				else{
					$temp['data'][] = 0;
				}
				 // Contoh nilai, bisa diganti dengan nilai dinamis
			}
		
			// Tambahkan array student yang lengkap ke dalam $responsible
			$responsible[] = $temp;
		}
		// die;
		$this->data['responsible'] =json_encode($responsible, JSON_PRETTY_PRINT);
		


		$schoolyearID = $this->session->userdata('defaultschoolyearID');

		$students 		= $this->student_m->get_order_by_student(array('schoolyearID' => $schoolyearID));
		$classes		= pluck($this->classes_m->get_classes(), 'obj', 'classesID');
		$teachers		= $this->teacher_m->get_teacher();
		$parents		= $this->parents_m->get_parents();
		$events			= $this->event_m->get_event();
		$questiongroup 	= $this->question_group_m->get_question_group();
		$questionlevel 	= $this->question_level_m->get_question_level();
		$questionbank 	= $this->question_bank_m->get_question_bank();
		$onlineexam 	= $this->online_exam_m->get_online_exam();
		$notice 		= $this->notice_m->get_notice();
		$studentgroup	= $this->studentgroup_m->get_studentgroup();

		$mainmenu     = $this->menu_m->get_order_by_menu();
		$allmenu 	  = pluck($mainmenu, 'icon', 'link');
		$allmenulang  = pluck($mainmenu, 'menuName', 'link');


		if($this->session->userdata('usertypeID') == 3) {
			$getLoginStudent = $this->student_m->get_single_student(array('username' => $this->session->userdata('username')));
			if(inicompute($getLoginStudent)) {
				$subjects	= $this->subject_m->get_order_by_subject(array('classesID' => $getLoginStudent->classesID));
			} else {
				$subjects = array();
			}
		} else {
			$subjects	= $this->subject_m->get_subject();
		}

		$deshboardTopWidgetUserTypeOrder = $this->session->userdata('master_permission_set');

		$this->data['dashboardWidget']['students'] 			= inicompute($students);
		$this->data['dashboardWidget']['classes']  			= inicompute($classes);
		$this->data['dashboardWidget']['teachers'] 			= inicompute($teachers);
		$this->data['dashboardWidget']['parents'] 			= inicompute($parents);
		$this->data['dashboardWidget']['subjects'] 			= inicompute($subjects);
		$this->data['dashboardWidget']['questiongroup'] 	= inicompute($questiongroup);
		$this->data['dashboardWidget']['questionlevel'] 	= inicompute($questionlevel);
		$this->data['dashboardWidget']['questionbank'] 		= inicompute($questionbank);
		$this->data['dashboardWidget']['onlineexam'] 		= inicompute($onlineexam);
		$this->data['dashboardWidget']['events'] 			= inicompute($events);
		$this->data['dashboardWidget']['notice']			= inicompute($notice);
		$this->data['dashboardWidget']['studentgroup']      = inicompute($studentgroup);
		$this->data['dashboardWidget']['allmenu'] 			= $allmenu;
		$this->data['dashboardWidget']['allmenulang'] 		= $allmenulang;

		$currentDate = strtotime(date('Y-m-d H:i:s'));
		$previousSevenDate = strtotime(date('Y-m-d 00:00:00', strtotime('-7 days')));

		$visitors = $this->loginlog_m->get_order_by_loginlog(array('login <= ' => $currentDate, 'login >= ' => $previousSevenDate));
		$showChartVisitor = array();
		foreach ($visitors as $visitor) {
			$date = date('j M',$visitor->login);
			if(!isset($showChartVisitor[$date])) {
				$showChartVisitor[$date] = 0;
			}
			$showChartVisitor[$date]++;
		}

		$this->data['showChartVisitor'] = $showChartVisitor;


		$userTypeID = $this->session->userdata('usertypeID');
		$userName = $this->session->userdata('username');
		$this->data['usertype'] = $this->session->userdata('usertype');

		if($userTypeID == 1) {
			$this->data['user'] = $this->systemadmin_m->get_single_systemadmin(array('username'  => $userName));
		} elseif($userTypeID == 2) {
			$this->data['user'] = $this->teacher_m->get_single_teacher(array('username'  => $userName));
		}  elseif($userTypeID == 3) {
			$this->data['user'] = $this->student_m->get_single_student(array('username'  => $userName));
		} elseif($userTypeID == 4) {
			$this->data['user'] = $this->parents_m->get_single_parents(array('username'  => $userName));
		} else {
			$this->data['user'] = $this->user_m->get_single_user(array('username'  => $userName));
		}

		$this->data['notices'] = $this->notice_m->get_order_by_notice(array('schoolyearID' => $schoolyearID));

		$this->data['events'] = $this->event_m->get_event();


		$this->data["subview"] = "dashboard/index";
		$this->load->view('_layout_main', $this->data);
	}

	public function update()
	{
		if($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1){
			$this->session->set_userdata('updatestatus', true);
			redirect(base_url('update/autoupdate'));
		}
	}

	public function remind()
	{
		if($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1){
			$this->session->set_userdata('updatestatus', false);
			redirect(base_url('dashboard/index'));
		}
	}
}

