<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Studentgroup extends Api_Controller
{
	public $load;
 public $session;
 public $lang;
 public $retdata;
 function __construct() {
        parent::__construct();
        $this->load->model("studentgroup_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('studentgroup', $language);
    }

	public function index_get() {
        $this->retdata['studentgroups'] = $this->studentgroup_m->get_order_by_studentgroup();
        $this->response([
			'status' => true,
			'message'=>'Status',
			'data' => $this->retdata
		],REST_Controller::HTTP_OK);
    }

}
