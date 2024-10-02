<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Setting extends Api_Controller
{
    public $retdata;
    function __construct() {
		parent::__construct();
		$this->load->model("setting_m");	
		$this->load->model("payment_gateway_m");	
		$this->load->model("payment_gateway_option_m");	
	}
    
	public function index_get() {

		$settings          = $this->setting_m->get_setting(1);
		$settingsInfo      = [];

		$settingsInfo['address']         = $settings->address;
		$settingsInfo['currency_code']   = $settings->currency_code;
		$settingsInfo['currency_symbol'] = $settings->currency_symbol;
		$settingsInfo['email']           = $settings->email;
		$settingsInfo['footer']          = $settings->footer;
		$settingsInfo['phone']           = $settings->phone;
		$settingsInfo['photo']           = $settings->photo;
		$settingsInfo['sname']           = $settings->sname;
		$settingsInfo['time_zone']       = $settings->time_zone;

		$this->retdata['setting'] = $settingsInfo;
		$this->retdata['paymentGateways'] = $this->payment_gateway_m->get_payment_gateway();
		$this->retdata['paymentGatewayInfo'] = $this->payment_gateway_option_m->get_payment_gateway_option();
		$this->response([
            'status' => true,
            'message'=> 'Success',
            'data'=> $this->retdata
        ],REST_Controller::HTTP_OK);
	}

    
}
