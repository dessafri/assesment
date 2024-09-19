<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Subject extends Api_Controller
{
    public $load;
    public $session;
    public $lang;
    public $student_m;
    public $retdata;
    public $parents_m;
    function __construct()
    {
        parent::__construct();
        $this->load->model("subject_m");
        $this->load->model("parents_m");
        $this->load->model("classes_m");
        $this->load->model("teacher_m");
        $this->load->model("student_m");
        $language = $this->session->userdata('lang');
        $this->lang->load('subject', $language);
    }

    public function index_get($id = null)
    {

        $usertypeID = $this->session->userdata("usertypeID");
        if ($usertypeID == 3) {
            $userID = $this->session->userdata('loginuserID');
            $student = $this->student_m->get_single_student(array('studentID' => $userID));
            $this->retdata['subjects'] = $this->subject_m->get_join_where_subject($student->classesID);
            $this->retdata['set'] = $student->classesID;
            $this->response([
                'status' => true,
                'message' => 'Success',
                'data' => $this->retdata
            ], REST_Controller::HTTP_OK);
        } elseif ($usertypeID == 4) {
            $schoolyearID = $this->session->userdata('defaultschoolyearID');
            $username = $this->session->userdata("username");
            $parent = $this->parents_m->get_single_parents(array('username' => $username));
            $this->retdata['students'] = $this->student_m->get_order_by_student(array('parentID' => $parent->parentsID, 'schoolyearID' => $schoolyearID));

            if ((int)$id !== 0) {
                $checkstudent = $this->student_m->get_single_student(array('studentID' => $id));
                if (inicompute($checkstudent)) {
                    $classesID = $checkstudent->classesID;
                    $this->retdata['set'] = $id;
                    $this->retdata['subjects'] = $this->subject_m->get_join_subject($classesID);
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
                $this->response([
                    'status' => true,
                    'message' => 'Success',
                    'data' => $this->retdata
                ], REST_Controller::HTTP_OK);
            }
        } elseif ((int)$id) {
            $this->retdata['set'] = $id;
            $this->retdata['classes'] = $this->classes_m->get_classes();
            $this->retdata['subjects'] = $this->subject_m->get_join_subject($id);
            $this->response([
                'status' => true,
                'message' => 'Success',
                'data' =>    $this->retdata
            ], REST_Controller::HTTP_OK);
        } else {
            $this->retdata['classes'] = $this->classes_m->get_classes();
            $this->response([
                'status' => true,
                'message' => 'Success',
                'data' =>    $this->retdata
            ], REST_Controller::HTTP_OK);
        }
    }
}
