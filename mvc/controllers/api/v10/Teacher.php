<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Teacher extends Api_Controller
{
    public $load;
    public $session;
    public $lang;
    public $retdata;
    public $teacher_m;
    public function __construct()
    {
        parent::__construct();
        $this->load->model("teacher_m");
        $this->load->model("document_m");
        $this->load->library('updatechecker');
        $language = $this->session->userdata('lang');
        $this->lang->load('teacher', $language);
    }

    public function index_get()
    {
        $this->retdata['teachers'] = $this->teacher_m->get_teacher();
        $this->response([
            'status' => true,
            'message' => 'Success',
            'data' => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    public function view_get($teacherID = 0)
    {
        $this->retdata['teacherID'] = $teacherID;
        if ((int)$teacherID !== 0) {
            $this->getView($teacherID);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Error 404',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
    //This function call view function
    private function getView($teacherID)
    {
        if ((int)$teacherID !== 0) {
            $teacherinfo = $this->teacher_m->get_teacher($teacherID);
            $this->teacherInfo($teacherinfo);
            $this->documentInfo($teacherinfo);

            if (inicompute($teacherinfo)) {
                $this->response([
                    'status' => true,
                    'message' => 'Success',
                    'data' => $this->retdata
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status' => FALSE,
                    'message' => 'Error 404',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Error 404',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function teacherinfo($teacherinfo)
    {
        $this->retdata['profile'] = inicompute($teacherinfo) ? $teacherinfo : [];
    }

    private function documentInfo($teacherinfo)
    {
        if (inicompute($teacherinfo)) {
            $this->retdata['documents'] = $this->document_m->get_order_by_document(array('usertypeID' => 2, 'userID' => $teacherinfo->teacherID));
        } else {
            $this->retdata['documents'] = [];
        }
    }
}
