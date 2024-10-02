<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Profile extends Api_Controller
{
	public $load;
 public $session;
 public $lang;
 public $student_m;
 public $retdata;
 public $section_m;
 public $classes_m;
 function __construct()
	{
		parent::__construct();
		$this->load->model('usertype_m');
		$this->load->model('section_m');
		$this->load->model("student_m");
		$this->load->model("parents_m");
		$this->load->model("teacher_m");
		$this->load->model("user_m");
		$this->load->model("systemadmin_m");
		$this->load->model('studentrelation_m');
		$this->load->model('document_m');
		$this->load->model('studentgroup_m');
		$this->load->model('subject_m');
		$this->load->model('online_exam_m');
		$this->load->model('online_exam_user_status_m');
		$this->load->model('classes_m');
		$language = $this->session->userdata('lang');
		$this->lang->load('profile', $language);
	}

	//profile index
	public function index_get()
	{
		$usertypeID  = $this->session->userdata("usertypeID");
		$loginuserID = $this->session->userdata('loginuserID');
		if ($usertypeID == 3) {
			$user = $this->student_m->get_single_student(array('studentID' => $loginuserID));
			$this->retdata['studentID'] = $user->studentID;
			$this->retdata['name'] = $user->name;
			$this->retdata['roll'] = $user->roll;
			$this->retdata['dob'] = $user->dob;
			$this->retdata['sex'] = $user->sex;
			$this->retdata['religion'] = $user->religion;
			$this->retdata['email'] = $user->email;
			$this->retdata['phone'] = $user->phone;
			$this->retdata['address'] = $user->address;
			$this->retdata['country'] = $user->country;
			$this->retdata['photo'] = imagelink($user->photo);
			$this->retdata['username'] = $user->username;
			$this->retdata['usertypeID'] = $user->usertypeID;
			$section        = $this->section_m->get_single_section(['sectionID' => $user->sectionID]);
			$this->retdata['sectionID'] = $section->sectionID;
			$this->retdata['sectionName'] = $section->section;
			$class        = $this->classes_m->get_single_classes(['classesID' => $user->classesID]);
			$this->retdata['classesID'] = $class->classesID;
			$this->retdata['classesName'] = $class->classes;
			$this->retdata['studentgroup']    = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $user->studentgroupID));
			$this->retdata['optionalsubject'] = $this->subject_m->get_single_subject(array('subjectID' => $user->optionalsubjectID));
			$this->retdata['examresults'] = $this->online_exam_user_status_m->get_order_by_online_exam_user_status(array('userID' => $this->session->userdata('loginuserID')));
			
			$this->response([
				'status'  => true,
				'message' => 'success',
				'data'    => $this->retdata
			], REST_Controller::HTTP_OK);

		}

		$this->response([
			'status'  => false,
			'message' => 'Error 404',
			'data'    => []
		], REST_Controller::HTTP_NOT_FOUND);
	}


}
