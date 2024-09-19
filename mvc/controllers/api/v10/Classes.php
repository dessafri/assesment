<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Classes extends Api_Controller
{
    public $load;
    public $session;
    public $lang;
    public $retdata;
    function __construct() {
		parent::__construct();
		$this->load->model('student_m');
		$this->load->model('studentrelation_m');
		$this->load->model('teacher_m');
        $this->load->model("classes_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('classes', $language);	
	}
    
	public function index_get() {
		$this->retdata['classes'] = $this->classes_m->get_join_classes();
		$this->response([
            'status' => true,
            'message'=> 'Success',
            'data'=> $this->retdata
        ],REST_Controller::HTTP_OK);
	}

    
}
