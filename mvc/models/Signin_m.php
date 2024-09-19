<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Signin_m extends MY_Model {

	public function __construct()
    {
        parent::__construct();
    }

    public function signout()
    {
        $this->session->sess_destroy();
    }

    public function loggedin()
    {
        return (bool) $this->session->userdata("loggedin");
    }
}