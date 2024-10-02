<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Event extends Api_Controller
{
    public $load;
    public $retdata;
    function __construct() {
		parent::__construct();
		$this->load->model("event_m");
	}

    public function index_get() {
		$this->retdata['events'] = $this->event_m->get_order_by_event();
		$this->response([
            'status' => true,
            'message'=> 'Success',
            'data'=>$this->retdata
        ],REST_Controller::HTTP_OK);
	}

}
