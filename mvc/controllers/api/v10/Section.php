<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Section extends Api_Controller
{
	public $load;
 public $session;
 public $lang;
 public $retdata;
 function __construct()
	{
		parent::__construct();
		$this->load->model("section_m");
		$this->load->model('classes_m');
		$this->load->model('studentrelation_m');
		$this->load->model('teacher_m');
		$language = $this->session->userdata('lang');
		$this->lang->load('section', $language);
	}

	public function index_get($id = null)
	{
		if ((int)$id !== 0) {
			$this->retdata['set'] = $id;
			$this->retdata['classes'] = $this->classes_m->get_classes();
			$this->retdata['sections'] = $this->section_m->get_join_section($id);
		} else {
			$this->retdata['classes'] = $this->classes_m->get_classes();
		}
		$this->response([
			'status' => true,
			'message' => 'Success',
			'data' => $this->retdata
		], REST_Controller::HTTP_OK);
	}
}
