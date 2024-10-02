<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Student extends Api_Controller
{
	public $load;
 public $session;
 public $lang;
 public $student_m;
 public $retdata;
 public $section_m;
 /**
  * @var array<string, mixed>
  */
 public $data;
 public $parents_m;
 public $uri;
 function __construct()
	{
		parent::__construct();
		$this->load->model("student_m");
		$this->load->model("parents_m");
		$this->load->model("teacher_m");
		$this->load->model("section_m");
		$this->load->model("classes_m");
		$this->load->model('studentrelation_m');
		$this->load->model('studentgroup_m');
		$this->load->model('studentextend_m');
		$this->load->model('document_m');
		$this->load->model('subject_m');
		$this->load->model('online_exam_user_status_m');
		$this->load->model('online_exam_m');
		$language = $this->session->userdata('lang');
		$this->lang->load('student', $language);
	}

	public function index_get()
	{
		$usertypeID   = $this->session->userdata('usertypeID');
		$loginuserID  = $this->session->userdata("loginuserID");
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if ($usertypeID == 3) {
			if (permissionChecker('student_view')) {
				$singleStudent = $this->student_m->get_single_student(array("studentID" => $loginuserID, 'schoolyearID' => $schoolyearID));
				if (inicompute($singleStudent)) {
					$this->retdata['students'] = $this->student_m->get_order_by_student(array('classesID' => $singleStudent->classesID, 'schoolyearID' => $schoolyearID));
					if (inicompute($this->retdata['students'])) {
						$sections = $this->section_m->get_order_by_section(array("classesID" => $singleStudent->classesID));
						if (inicompute($sections)) {
							foreach ($sections as $section) {
								$this->retdata['allsection'][$section->sectionID] = $this->student_m->get_order_by_student(array('classesID' => $singleStudent->classesID, "sectionID" => $section->sectionID, 'schoolyearID' => $schoolyearID));
							}
						}
					} else {
						$this->retdata['students'] = NULL;
					}
					$this->retdata['sections'] = $sections;
					$this->response([
						'status' => true,
						'message' => 'Success',
						'data' => $this->retdata
					], REST_Controller::HTTP_OK);
				} else {
					$this->response([
						'status' => false,
						'message' => 'Error 404',
						'data' => []
					], REST_Controller::HTTP_NOT_FOUND);
				}
			} else {
				$loginuserID = $this->session->userdata("loginuserID");
				$student = $this->student_m->get_single_student(array('studentID' => $loginuserID, 'schoolyearID' => $schoolyearID));
				if (inicompute($student)) {
					$this->data['classesID'] = $student->classesID;
					$this->data['studentID'] = $student->studentID;
					$this->getView($student->studentID, $student->classesID);
				} else {
					$this->response([
						'status' => false,
						'message' => 'Error 404',
						'data' => []
					], REST_Controller::HTTP_NOT_FOUND);
				}
			}
		} elseif ($usertypeID == 4) {
			$parents      = $this->parents_m->get_single_parents(array('parentsID' => $loginuserID));
			if (inicompute($parents)) {
				$this->retdata['students'] = $this->student_m->get_order_by_student(array('parentID' => $loginuserID, 'schoolyearID' => $schoolyearID));
				$this->response([
					'status' => true,
					'message' => 'Success',
					'data' => $this->retdata
				], REST_Controller::HTTP_OK);
			} else {
				$this->response([
					'status' => false,
					'message' => 'Error 404',
					'data' => []
				], REST_Controller::HTTP_NOT_FOUND);
			}
		} else {
			$classesID = htmlentities((string) escapeString($this->uri->segment(3)));
			$this->retdata['students'] = $this->student_m->get_order_by_student(array('classesID' => $classesID, 'schoolyearID' => $schoolyearID));
			if (inicompute($this->retdata['students'])) {
				$sections = $this->section_m->get_order_by_section(array("classesID" => $classesID));
				if (inicompute($sections)) {
					foreach ($sections as $section) {
						$this->retdata['allsection'][$section->sectionID] = $this->student_m->get_order_by_student(array('classesID' => $classesID, "sectionID" => $section->sectionID, 'schoolyearID' => $schoolyearID));
					}
				}
				$this->retdata['sections'] = $sections;
			} else {
				$this->retdata['students'] = [];
			}
			$this->retdata['set'] = $classesID;
			$this->retdata['classes'] = $this->classes_m->get_classes();

			$this->response([
				'status' => true,
				'message' => 'Success',
				'data' => $this->retdata
			], REST_Controller::HTTP_OK);
		}
	}

	public function view_get($classesID,$studentID) {
		
		$usertypeID   = $this->session->userdata('usertypeID');
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		$username     = $this->session->userdata("username");
		
		$this->retdata['classesID'] = $classesID;
		$this->retdata['studentID'] = $studentID;
		
		if($usertypeID == 3) {
			if(permissionChecker('student_view')) {
				if((int)$studentID && (int)$classesID) {
					$originalStudent = $this->student_m->get_single_student(array("username" => $username));
					if(inicompute($originalStudent)) {
						$student = $this->student_m->get_single_student(array('studentID' => $studentID, 'schoolyearID' => $schoolyearID));
						if(inicompute($student)) {
							if($originalStudent->classesID == $student->classesID) {
								$this->getView($studentID,$classesID);
							} else {
								$this->response([
									'status' => false,
									'success'=> 'Error 404',
									'data' => []
								],REST_Controller::HTTP_NOT_FOUND);
							}
						} else {
							$this->response([
								'status' => false,
								'success'=> 'Error 404',
								'data' => []
							],REST_Controller::HTTP_NOT_FOUND);
						}
					} else {
						$this->response([
							'status' => false,
							'success'=> 'Error 404',
							'data' => []
						],REST_Controller::HTTP_NOT_FOUND);
					}
				} else {
					$this->response([
						'status' => false,
						'success'=> 'Error 404',
						'data' => []
					],REST_Controller::HTTP_NOT_FOUND);
				}
			} else {
				$student = $this->student_m->get_single_student(array('username' => $username, 'schoolyearID' => $schoolyearID));
				if(inicompute($student)) {
					$this->getView($student->studentID,$student->classesID);
				} else {
					$this->response([
						'status' => false,
						'success'=> 'Error 404',
						'data' => []
					],REST_Controller::HTTP_NOT_FOUND);
				}
			}
		} elseif($usertypeID == 4) {
			$parents = $this->parents_m->get_single_parents(array('username' => $username));
			if(inicompute($parents)) {
				if((int)$studentID && (int)$classesID) {
					$checkstudent = $this->student_m->get_single_student(array('studentID' => $studentID, 'schoolyearID' => $schoolyearID));
					if(inicompute($checkstudent)) {
						if($checkstudent->parentID == $parents->parentsID) {
							$this->getView($studentID, $classesID);
						} else {
							$this->response([
								'status' => false,
								'success'=> 'Error 404',
								'data' => []
							],REST_Controller::HTTP_NOT_FOUND);
						}
					} else {
						$this->retdata["subview"] = "error";
						$this->load->view('_layout_main', $this->retdata);
					}
				} else {
					$this->response([
						'status' => false,
						'success'=> 'Error 404',
						'data' => []
					],REST_Controller::HTTP_NOT_FOUND);
				}
			} else {
				$this->response([
					'status' => false,
					'success'=> 'Error 404',
					'data' => []
				],REST_Controller::HTTP_NOT_FOUND);
			}
		} else {
		
			$student = $this->student_m->get_single_student(array('studentID' => $studentID, 'schoolyearID' => $schoolyearID));
			if(inicompute($student)) {
				$this->getView($studentID, $classesID);
			} else {
				$this->response([
					'status' => false,
					'success'=> 'Error 404',
					'data' => []
				],REST_Controller::HTTP_NOT_FOUND);
			}
		}
	}

	//This function Call in index,view, function
	private function getView($studentID, $classesID)
	{
		$schoolyearID = $this->session->userdata('defaultschoolyearID');
		if ((int)$studentID && (int)$classesID) {
			$studentInfo = $this->student_m->get_single_student(array('studentID' => $studentID, 'classesID' => $classesID, 'schoolyearID' => $schoolyearID));

			$this->basicInfo($studentInfo);
			$this->parentInfo($studentInfo);
			$this->examInfo($studentInfo);
			$this->documentInfo($studentInfo);

			if (inicompute($studentInfo)) {
				$this->response([
					'status'    => true,
					'message'   => 'Success',
					'data'      => $this->retdata
				], REST_Controller::HTTP_OK);
			} else {
				$this->response([
					'status' => false,
					'message' => 'Error 404',
					'data' => []
				], REST_Controller::HTTP_NOT_FOUND);
			}
		}
	}
	//This function Call getView function
	private function basicInfo($studentInfo)
	{
		if (inicompute($studentInfo)) {
			$this->retdata['profile'] = $studentInfo;
			$this->retdata['usertype'] = $this->usertype_m->get_single_usertype(array('usertypeID' => $studentInfo->usertypeID));
			$this->retdata['class'] = $this->classes_m->get_single_classes(array('classesID' => $studentInfo->classesID));
			$this->retdata['section'] = $this->section_m->get_single_section(array('sectionID' => $studentInfo->sectionID));

			$this->retdata['group'] = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $studentInfo->studentgroupID));
			$this->retdata['optionalsubject'] = $this->subject_m->get_single_subject(array('subjectID' => $studentInfo->optionalsubjectID));
		} else {
			$this->retdata['profile'] = [];
		}
	}
	//This function Call getView function
	private function parentInfo($studentInfo)
	{
		if (inicompute($studentInfo)) {
			$this->retdata['parents'] = $this->parents_m->get_single_parents(array('parentsID' => $studentInfo->parentID));
		} else {
			$this->retdata['parents'] = [];
		}
	}
	//This function Call getView function
	private function examInfo($studentInfo)
	{
		$this->retdata['onlineexams'] = pluck($this->online_exam_m->get_online_exam(), 'obj', 'onlineExamID');
		$this->retdata['examresults'] = $this->online_exam_user_status_m->get_order_by_online_exam_user_status(array('userID' => $studentInfo->studentID));
	}
	//This function Call getView function
	private function documentInfo($studentInfo)
	{
		if (inicompute($studentInfo)) {
			$this->retdata['documents'] = $this->document_m->get_order_by_document(array('usertypeID' => 3, 'userID' => $studentInfo->studentID));
		} else {
			$this->retdata['documents'] = [];
		}
	}
}
