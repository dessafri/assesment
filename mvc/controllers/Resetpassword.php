<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resetpassword extends Admin_Controller {
public $load;
 public $session;
 public $lang;
 public $data;
 public $input;
 public $form_validation;
 public $resetpassword_m;
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
	function __construct() {
		parent::__construct();
		$this->load->model("user_m");
		$this->load->model("teacher_m");
		$this->load->model("parents_m");
		$this->load->model("student_m");
		$this->load->model('usertype_m');
		$this->load->model("systemadmin_m");
		$this->load->model("resetpassword_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('resetpassword', $language);	
	}

	protected function rules() {
		return array(
			array(
				'field' => 'usertypeID', 
				'label' => $this->lang->line("resetpassword_usertype"), 
				'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
			), 
			array(
				'field' => 'userID', 
				'label' => $this->lang->line("resetpassword_user"),
				'rules' => 'trim|required|xss_clean|numeric|callback_unique_data'
			), 
			array(
				'field' => 'new_password', 
				'label' => $this->lang->line("resetpassword_new_password"),
				'rules' => 'trim|required|xss_clean|min_length[4]|max_length[40]'
			), 
			array(
				'field' => 're_password', 
				'label' => $this->lang->line("resetpassword_re_password"), 
				'rules' => 'trim|required|xss_clean|min_length[4]|max_length[40]|matches[new_password]'
			)
		);
	}

	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);
		$this->data['users']      = [];
		$this->data['tableInfo']  = [];
		$this->data['usertypes']  = $this->usertype_m->get_usertype();
		if($_POST !== []) {
			$usertypeID = $this->input->post('usertypeID');
			if((int)$usertypeID !== 0) {
				$tableInfo = $this->_returnTable($usertypeID);
				$tablename = $tableInfo['table'];
				$tableID   = $tableInfo['tableID'];

				$this->data['tableInfo'] = $tableInfo;
				$this->data['users']     = $this->resetpassword_m->get_username($tablename, array('usertypeID' => $usertypeID));
			}

			$rules = $this->rules();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) { 
				$this->data["subview"] = "resetpassword/index";
				$this->load->view('_layout_main', $this->data);			
			} else {
				$userID            = $this->input->post('userID');
				$array['password'] = $this->resetpassword_m->hash($this->input->post("new_password"));
				$this->resetpassword_m->update_resetpassword($tablename, $array, $tableID, $userID);
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url("resetpassword/index"));
			}
		} else {
			$this->data["subview"] = "resetpassword/index";
			$this->load->view('_layout_main', $this->data);
		}

	}

	public function get_user() {
		echo "<option value='0'>". $this->lang->line("resetpassword_select_user") ."</option>";
		if($_POST !== []) {
			$usertypeID = $this->input->post('usertypeID');
			if((int)$usertypeID !== 0) {
				$tableInfo = $this->_returnTable($usertypeID);
				$tablename = $tableInfo['table'];
				$tableID   = $tableInfo['tableID'];

				$users = $this->resetpassword_m->get_username($tablename, array('usertypeID' => $usertypeID));
				if(inicompute($users)) {
					foreach ($users as $user) {
						if($tablename == 'systemadmin') {
							if($user->systemadminID != 1) {
								echo "<option value='".$user->systemadminID."'>".$user->name." ( ".$user->username." )" ."</option>";
							}
						} else {
							echo "<option value='".$user->$tableID."'>".$user->name." ( ".$user->username." )" ."</option>";
						}
					}
				}
			}
		}
	}

	public function unique_data($data) {
		if($data == 0) {
			$this->form_validation->set_message("unique_data", "The %s field is required.");
	     	return FALSE;
		}
		return TRUE;
	}

	private function _returnTable($usertypeID) {
		$retArray  = [];
		if($usertypeID == 1) {
			$retArray['table']   = "systemadmin";
			$retArray['tableID'] = "systemadminID";
		} elseif($usertypeID == 2) {
			$retArray['table']   = "teacher";
			$retArray['tableID'] = "teacherID";
		} elseif($usertypeID == 3) {
			$retArray['table']   = "student";
			$retArray['tableID'] = "studentID";
		} elseif($usertypeID == 4) { 
			$retArray['table']   = "parents";
			$retArray['tableID'] = "parentsID";
		} else {
			$retArray['table']   = "user";
			$retArray['tableID'] = "userID";
		}
		return $retArray;
	}

}

/* End of file class.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/class.php */