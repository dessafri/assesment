<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Reset extends REST_Controller
{

	public $retdata;
 function __construct()
	{
		parent::__construct();
		$this->load->helper("form");
		$this->load->library("email");
		$this->load->model("reset_m");
		$this->load->library("form_validation");
		$this->load->helper("url");
		$this->load->library('session');
	}


	public function getOTP_post()
	{
		$this->load->database();
		$this->load->model("reset_m");
		$this->load->library('session');
		$array = array();
		$reset_key = "";
		$tmp_url = "";
		$i = 0;
		$this->data['siteinfos'] = $this->reset_m->get_site();

		$email = inputCall('email');
		$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
		foreach ($tables as $table) {
			$dbuser = $this->reset_m->get_table_users($table, $email);
			if (inicompute($dbuser)) {
				$reset_key = rand(1000, 9999);
				$array['permition'][$i] = 'yes';
			} else {
				$array['permition'][$i] = 'no';
			}
			$i++;
		}

		if (in_array('yes', $array['permition'])) {
			$dbreset = $this->reset_m->get_reset();
			if (inicompute($dbreset)) {
				if ($this->db->truncate('reset')) {
					$this->reset_m->insert_reset(array('keyID' => $reset_key, 'email' => $email));
				} else {
					$this->response([
						'status' => 403,
						'message' => 'Reset Access Off',
						'data' => []
					], REST_Controller::HTTP_NOT_FOUND);
				}
			} else {
				$this->reset_m->insert_reset(array('keyID' => $reset_key, 'email' => $email));
			}
			$this->email->from($this->data['siteinfos']->email, $this->data['siteinfos']->sname);
			$this->email->to($email);
			$this->email->subject('Reset Password');
			$message = 'Your OTP is  -> ' . $reset_key;
			$this->email->message($message);
			if ($this->email->send()) {
				$this->response([
					'status' => true,
					'message' => 'Email Send',
					'data' => $this->retdata
				], REST_Controller::HTTP_OK);
			} else {
				$this->response([
					'status' => 421,
					'message' => 'Email Not Send',
					'data' => []
				], REST_Controller::HTTP_NOT_FOUND);
			}
		} else {
			$this->response([
				'status' => 404,
				'message' => 'User Not Found',
				'data' => []
			], REST_Controller::HTTP_NOT_FOUND);
		}
		$this->response([
			'status' => 421,
			'message' => 'User Not Found',
			'data' => []
		], REST_Controller::HTTP_NOT_FOUND);
	}

	public function verifyOTP_post()
	{
		$dbreset = $this->reset_m->get_reset(['keyID' => inputCall('otp')]);
		if ($dbreset) {
			$this->retdata['email'] = $dbreset->email;
			$this->response([
				'status' => true,
				'message' => 'OTP Verified',
				'data' => $this->retdata
			], REST_Controller::HTTP_OK);
		}
		$this->response([
			'status' => 404,
			'message' => 'Invalid OTP',
			'data' => []
		], REST_Controller::HTTP_NOT_FOUND);
	}


	public function changePassword_post()
	{
		$password = inputCall('newpassword');
		$email = inputCall('email');
		$tables = array('student' => 'student', 'parents' => 'parents', 'teacher' => 'teacher', 'user' => 'user', 'systemadmin' => 'systemadmin');
		$notFound = false;
		foreach ($tables as $table) {

			$dbuser = $this->reset_m->get_table_users($table, $email);
			if (inicompute($dbuser)) {
				$data = array('password' => $this->reset_m->hash($password));
				$this->db->update($table, $data, "email = '" . $email . "'");
				$this->db->truncate('reset');
				$this->response([
					'status' => true,
					'message' => 'Password Reseted',
					'data' => $this->retdata
				], REST_Controller::HTTP_OK);
			} else {
				$notFound = true;
			}
		}


		if ($notFound) {
			$this->response([
				'status' => 404,
				'message' => 'User Not Found',
				'data' => []
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}
}

/* End of file class.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/class.php */