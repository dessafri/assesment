<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Parents extends Api_Controller
{
    public $load;
    public $session;
    public $lang;
    public $retdata;
    public $parents_m;
    public $db;
    public function __construct () {
		parent::__construct();
		$this->load->model("parents_m");
		$this->load->model("student_m");
		$this->load->model("section_m");
		$this->load->model("document_m");
		$this->load->model("usertype_m");
		$this->load->model("classes_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('parents', $language);
	}
    

    public function index_get() {
		$this->retdata['parents'] = $this->parents_m->get_parents();
		$this->retdata["subview"] = "parents/index";
        $this->response([
            'status' => true,
            'message'=> 'Success',
            'data' => $this->retdata
        ],REST_Controller::HTTP_OK);
	}

    public function view_get($parentsID = 0) {
    
		$this->retdata['parentsID'] = $parentsID;
  
		if ((int)$parentsID !== 0) {
			$this->getView($parentsID);
		} else {
            $this->response([
                'status' => false,
                'message'=> 'Error 404',
                'data' => []
            ],REST_Controller::HTTP_NOT_FOUND);
		}
	}

	private function getView($parentsID) {
     
		if ((int)$parentsID !== 0) {
			$parents = $this->parents_m->get_parents($parentsID);
           
			$this->plucInfo();
			$this->basicInfo($parents);
			$this->childrenInfo($parents);
			$this->documentInfo($parents);
          
			if(inicompute($parents)) {
                $this->response([
                    'status'  => true,
                    'message' => 'Success',
                    'data'    => $this->retdata
                ],REST_Controller::HTTP_OK);
			} else {
                $this->response([
                    'status'  => false,
                    'message' => 'Error 404',
                    'data'    => []
                ],REST_Controller::HTTP_NOT_FOUND);
			}
		} else {
            $this->response([
                'status'  => false,
                'message' => 'Error 404',
                'data'    => []
            ],REST_Controller::HTTP_NOT_FOUND);
		}
	}

	private function basicInfo($parents) {
		$this->retdata['profile'] = inicompute($parents) ? $parents : [];
	}

	private function childrenInfo($parents) {
		$this->retdata['childrens'] = [];
		if(inicompute($parents)) {
			$schoolyearID = $this->session->userdata('defaultschoolyearID');
			$this->db->order_by('student.classesID', 'asc');
			$this->retdata['childrens'] = $this->student_m->general_get_order_by_student(array('parentID' => $parents->parentsID, 'schoolyearID' => $schoolyearID));
		}
	}

    private function plucInfo() {
        $this->retdata['usertypes'] = pluck($this->usertype_m->get_usertype(),'usertype','usertypeID');
        $this->retdata['classes']   = pluck($this->classes_m->general_get_classes(),'classes','classesID');
		$this->retdata['sections']  = pluck($this->section_m->get_section(),'section','sectionID');
    }

    private function documentInfo($parents) {
		if(inicompute($parents)) {
			$this->retdata['documents'] = $this->document_m->get_order_by_document(array('usertypeID' => 4, 'userID' => $parents->parentsID));
		} else {
			$this->retdata['documents'] = [];
		}
	}

  
}
