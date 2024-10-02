<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Dashboard extends Api_Controller
{
	public $load;
 public $session;
 public $lang;
 public $student_m;
 public $classes_m;
 public $teacher_m;
 public $parents_m;
 public $event_m;
 public $question_group_m;
 public $question_level_m;
 public $question_bank_m;
 public $online_exam_m;
 public $notice_m;
 public $studentgroup_m;
 public $menu_m;
 public $retdata;
 public $subject_m;
 public $loginlog_m;
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
		$this->load->model('question_group_m');
		$this->load->model('question_level_m');
		$this->load->model('question_bank_m');
		$this->load->model('online_exam_m');
		$this->load->model('studentgroup_m');
		$this->load->model('loginlog_m');
		$this->load->model('menu_m');

		$language = $this->session->userdata('lang');
		$this->lang->load('dashboard', $language);
	}

	public function index_get() {
	
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

		if((config_item('demo') === FALSE) && ($this->retdata['siteinfos']->auto_update_notification == 1) && ($this->session->userdata('usertypeID') == 1) && ($this->session->userdata('loginuserID') == 1)) {
			$this->retdata['versionChecking'] = $this->session->userdata('updatestatus') === null ? $this->checkUpdate() : 'none';
		} else {
			$this->retdata['versionChecking'] = 'none';
		}


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

		$this->retdata['dashboardWidget']['students'] 			= inicompute($students);
		$this->retdata['dashboardWidget']['classes']  			= inicompute($classes);
		$this->retdata['dashboardWidget']['teachers'] 			= inicompute($teachers);
		$this->retdata['dashboardWidget']['parents'] 			= inicompute($parents);
		$this->retdata['dashboardWidget']['subjects'] 			= inicompute($subjects);
		$this->retdata['dashboardWidget']['questiongroup'] 	= inicompute($questiongroup);
		$this->retdata['dashboardWidget']['questionlevel'] 	= inicompute($questionlevel);
		$this->retdata['dashboardWidget']['questionbank'] 		= inicompute($questionbank);
		$this->retdata['dashboardWidget']['onlineexam'] 		= inicompute($onlineexam);
		$this->retdata['dashboardWidget']['events'] 			= inicompute($events);
		$this->retdata['dashboardWidget']['notice']			= inicompute($notice);
		$this->retdata['dashboardWidget']['studentgroup']      = inicompute($studentgroup);
		$this->retdata['dashboardWidget']['allmenu'] 			= $allmenu;
		$this->retdata['dashboardWidget']['allmenulang'] 		= $allmenulang;

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

		$this->retdata['showChartVisitor'] = $showChartVisitor;
		

		$userTypeID = $this->session->userdata('usertypeID');
		$userName = $this->session->userdata('username');
		$this->retdata['usertype'] = $this->session->userdata('usertype');
		
		if($userTypeID == 1) {
			$this->retdata['user'] = $this->systemadmin_m->get_single_systemadmin(array('username'  => $userName));
		} elseif($userTypeID == 2) {
			$this->retdata['user'] = $this->teacher_m->get_single_teacher(array('username'  => $userName));
		}  elseif($userTypeID == 3) {
			$this->retdata['user'] = $this->student_m->get_single_student(array('username'  => $userName));
		} elseif($userTypeID == 4) {
			$this->retdata['user'] = $this->parents_m->get_single_parents(array('username'  => $userName));
		} else {
			$this->retdata['user'] = $this->user_m->get_single_user(array('username'  => $userName));
		}

		$this->retdata['notices'] = $this->notice_m->get_order_by_notice(array('schoolyearID' => $schoolyearID));

		$this->retdata['events'] = $this->event_m->get_event();

		$this->response([
			'status' => true,
			'message' => 'Success',
			'data' => $this->retdata
		],REST_Controller::HTTP_OK);
	}
}
