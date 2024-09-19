<?php

if(!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');

class PaymentWebview extends MY_Controller
{
    public $load;
    public $lang;
    public $data;
    public $uri;
    public $setting_m;
    public $db;
    public $session;
    public $site_m;
    public $subject_m;
    public $input;
    public $form_validation;
    public $online_exam_m;
    public $online_exam_payment_m;
    /*
    | -----------------------------------------------------
    | PRODUCT NAME: 	INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:			INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:			info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:		RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:			http://inilabs.net
    | -----------------------------------------------------
    */
    /**
     * @var \PaymentGateway
     */
    public $payment_gateway;
    public $payment_gateway_array;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('session');
        $this->load->helper('form');
        $this->load->model('online_exam_m');
        $this->load->model('online_exam_payment_m');
        $this->load->model('online_exam_question_m');
        $this->load->model('instruction_m');
        $this->load->model('question_bank_m');
        $this->load->model('question_option_m');
        $this->load->model('question_answer_m');
        $this->load->model('online_exam_user_answer_m');
        $this->load->model('online_exam_user_status_m');
        $this->load->model('online_exam_user_answer_option_m');
        $this->load->model('student_m');
        $this->load->model('classes_m');
        $this->load->model('student_m');
        $this->load->model('section_m');
        $this->load->model('subject_m');
        $this->load->model('payment_gateway_m');
        $this->load->model('payment_gateway_option_m');
        $this->load->model("site_m");
        $this->load->model('setting_m');


//        $language = $this->session->userdata('lang');
        $this->lang->load('topbar_menu', 'english');

        $this->lang->load('take_exam', 'english');

        $this->payment_gateway       = new PaymentGateway();
        $this->payment_gateway_array = pluck($this->payment_gateway_m->get_order_by_payment_gateway(['status' => 1]), 'status', 'slug');
    }

    public function index()
    {

        $this->data['headerassets'] = [
            'css' => [
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css'
            ],
            'js' => [
                'assets/select2/select2.js'
            ]
        ];
        $loginuserID = htmlentities((string) escapeString($this->uri->segment(3)));
        $usertypeID = htmlentities((string) escapeString($this->uri->segment(4)));
        $this->data['loginuserID'] = $loginuserID;
        $this->data['usertypeID'] = $usertypeID;
        $this->data['onlineExamID'] = htmlentities((string) escapeString($this->uri->segment(5)));
        if($loginuserID && $usertypeID && $this->data['onlineExamID']) {
            $setting = $this->setting_m->get_setting();
            $userFoundInfo = [];

            $user = $this->db->get_where('student', ["studentID" => $loginuserID, 'active' => 1]);
            $userInfo = $user->row();
            if (customCompute($userInfo)) {
                $userFoundInfo = $userInfo;
            }


            if (customCompute($userFoundInfo)) {
                $sessionArray = [
                    'loginuserID' => $userFoundInfo->studentID,
                    'name' => $userFoundInfo->name,
                    'email' => $userFoundInfo->email,
                    'usertypeID' => $usertypeID,
                    'usertype' => 'student',
                    'username' => $userFoundInfo->username,
                    'password' => $userFoundInfo->password,
                    'photo' => $userFoundInfo->photo,
                    'lang' => $setting->language,
                    'defaultschoolyearID' => $setting->school_year,
                    "loggedin" => true,
                    "varifyvaliduser" => true,
                    "paymentWebview" => true,
                ];
            }
            $this->session->set_userdata($sessionArray);
            $siteInfo = $this->site_m->get_site();
            $this->data["siteinfos"] = $siteInfo;


            $this->data['userSubjectPluck'] = [];
            if ($usertypeID == '3') {
                $this->data['student'] = $this->student_m->get_single_student(['studentID' => $loginuserID]);
                if (inicompute($this->data['student'])) {
                    $this->data['userSubjectPluck'] = pluck($this->subject_m->get_order_by_subject([
                        'classesID' => $this->data['student']->classesID,
                        'type' => 1
                    ]), 'subjectID', 'subjectID');
                    $optionalSubject = $this->subject_m->get_single_subject([
                        'type' => 0,
                        'subjectID' => $this->data['student']->optionalsubjectID
                    ]);
                    if (inicompute($optionalSubject)) {
                        $this->data['userSubjectPluck'][$optionalSubject->subjectID] = $optionalSubject->subjectID;
                    }
                }
            }

            $this->data['payment_settings'] = $this->payment_gateway_m->get_order_by_payment_gateway(['status' => 1]);
            $this->data['payment_options'] = pluck($this->payment_gateway_option_m->get_payment_gateway_option(), 'payment_value', 'payment_option');

            $this->data['payments'] = pluck_multi_array($this->online_exam_payment_m->get_order_by_online_exam_payment([
                'usertypeID' => $usertypeID,
                'userID' => $loginuserID
            ]), 'obj', 'online_examID');
            $this->data['paindingpayments'] = pluck($this->online_exam_payment_m->get_order_by_online_exam_payment([
                'usertypeID' => $usertypeID,
                'userID' => $loginuserID,
                'status' => 0
            ]), 'obj', 'online_examID');
            $this->data['examStatus'] = pluck($this->online_exam_user_status_m->get_order_by_online_exam_user_status(['userID' => $loginuserID]), 'obj', 'onlineExamID');
            $this->data['usertypeID'] = $usertypeID;
            $this->data['onlineExams'] = $this->online_exam_m->get_order_by_online_exam([
                'usertypeID' => $usertypeID,
                'published' => 1
            ]);

            $this->data['validationErrors'] = [];
            $this->data['validationOnlineExamID'] = 0;
            $this->data['form_validation'] = 'No';
            if ($_POST !== []) {
                $rules = $this->payment_rules($this->input->post('payment_method'));
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == FALSE) {
                    $this->data['validationOnlineExamID'] = $this->input->post('onlineExamID');
                    $this->data['validationErrors'] = $this->form_validation->error_array();
                    $this->data['form_validation'] = validation_errors();
                    $this->data["subview"] = "paymentWebview/index";
                    $this->load->view('paymentWebview/index', $this->data);
                } elseif ($this->input->post('onlineExamID')) {
                    $invoice_data = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $this->input->post('onlineExamID')]);
                    if (($invoice_data->paid == 1) && ((float)$invoice_data->cost == 0)) {
                        $this->session->set_flashdata('error', 'Exam amount can not be zero');
                        redirect(base_url('paymentWebview/index/'.$loginuserID.'/'.$usertypeID.'/'.$this->input->post('onlineExamID')));
                    }
                    if (($invoice_data->examStatus == 1) && ($invoice_data->paid == 1) && isset($this->data['paindingpayments'][$invoice_data->onlineExamID])) {
                        $this->session->set_flashdata('error', 'This exam price already paid');
                        redirect(base_url('paymentWebview/index/'.$loginuserID.'/'.$usertypeID.'/'.$this->input->post('onlineExamID')));
                    }
                    $this->payment_gateway->gateway($this->input->post('payment_method'))->payment($this->input->post(), $invoice_data);
                } else {
                    $this->session->set_flashdata('error', 'Exam does not found');
                    redirect(base_url('paymentWebview/index/'.$loginuserID.'/'.$usertypeID.'/'.$this->input->post('onlineExamID')));
                }
            } else {
                $this->data["subview"] = "paymentWebview/index";
                $this->load->view('paymentWebview/index', $this->data);
            }
        } else {
            $this->data["subview"] = "paymentWebview/cancel";
            $this->load->view('paymentWebview/cancel', $this->data);
        }


    }

    public function paymentSuccess(){
        $this->data["subview"] = "paymentWebview/payment";
        $this->load->view('paymentWebview/payment', $this->data);
    }
    public function randAssociativeArray( $array, $number = 0 )
    {
        $returnArray = [];
        $countArray  = inicompute($array);
        if($number > $countArray || $number == 0) {
            $number = $countArray;
        }

        if ($countArray == 1) {
            $randomKey[] = 0;
        } elseif (inicompute($array)) {
            $randomKey = array_rand($array, $number);
        } else {
            $randomKey = [];
        }

        if(is_array($randomKey)) {
            shuffle($randomKey);
        }

        if(inicompute($randomKey)) {
            foreach($randomKey as $key) {
                $returnArray[] = $array[$key];
            }
            return $returnArray;
        } else {
            return $array;
        }
    }

    public function get_payment_info() //done
    {
        $onlineExamID = $this->input->post('onlineExamID');

        $retArray['status']        = false;
        $retArray['payableamount'] = 0.00;
            if(!empty($onlineExamID) && (int)$onlineExamID && $onlineExamID > 0) {
                $onlineExam = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
                if(inicompute($onlineExam)) {
                    $retArray['status']        = true;
                    $retArray['payableamount'] = sprintf("%.2f", $onlineExam->cost);
                }
            }

        echo json_encode($retArray);
        exit;
    }

    protected function payment_rules( $method ) : array //done
    {
        return $this->payment_gateway->gateway($method)->payment_rules([
            [
                'field' => 'payment_method',
                'label' => $this->lang->line("take_exam_payment_method"),
                'rules' => 'trim|required|xss_clean|max_length[11]|callback_unique_payment_method'
            ],
            [
                'field' => 'paymentAmount',
                'label' => $this->lang->line("take_exam_payment_amount"),
                'rules' => 'trim|required|xss_clean|max_length[16]'
            ]
        ]);
    }

    public function unique_payment_method() : bool  //done
    {
        if($this->input->post('payment_method') === 'select') {
            $this->form_validation->set_message("unique_payment_method", "Payment method is required.");
            return false;
        } else {
            if(!$this->payment_gateway->gateway($this->input->post('payment_method'))->status()) {
                $this->form_validation->set_message("unique_payment_method", "The Payment method is disable now, try other payment method system");
                return false;
            }
            return true;
        }
    }

    public function paymentChecking()  //done
    {
        $onlineExamID        = $this->input->post('onlineExamID');
        $status              = 'FALSE';
        $paymentExpireStatus = TRUE;
        if($onlineExamID > 0) {
            $onlineExam = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
            if (inicompute($onlineExam) && (($onlineExam->examStatus == 2) && ($onlineExam->paid == 1))) {
                if($onlineExam->examTypeNumber == '4') {
                    $presentDate   = strtotime(date('Y-m-d'));
                    $examStartDate = strtotime((string) $onlineExam->startDateTime);
                    $examEndDate   = strtotime((string) $onlineExam->endDateTime);
                } elseif($onlineExam->examTypeNumber == '5') {
                    $presentDate   = strtotime(date('Y-m-d H:i:s'));
                    $examStartDate = strtotime((string) $onlineExam->startDateTime);
                    $examEndDate   = strtotime((string) $onlineExam->endDateTime);
                }
                if(($onlineExam->examTypeNumber == '4' || $onlineExam->examTypeNumber == '5') && ($presentDate > $examStartDate && $presentDate > $examEndDate)) {
                    $paymentExpireStatus = FALSE;
                }
                if($paymentExpireStatus) {
                    $onlineExamPayments = $this->online_exam_payment_m->get_single_online_exam_payment_only_first_row([
                        'online_examID' => $onlineExamID,
                        'status'        => 0,
                        'usertypeID'    => $this->session->userdata('usertypeID'),
                        'userID'        => $this->session->userdata('loginuserID')
                    ]);
                    if($onlineExamPayments->online_exam_paymentID == NULL) {
                        $status = 'TRUE';
                    }
                }
            }
        }

        echo $status;
    }

    public function success()
    {
        if(isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->success();
        }
    }

    public function cancel()
    {
        if(isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->cancel();
        }
    }

    public function fail()
    {
        if(isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->fail();
        }
    }
}
