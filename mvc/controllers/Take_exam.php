<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');

class Take_exam extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $data;
    public $subject_m;
    public $input;
    public $form_validation;
    public $online_exam_m;
    public $uri;
    public $online_exam_user_status_m;
    public $online_exam_question_m;
    public $db;
    public $question_bank_m;
    public $question_option_m;
    public $question_level_report_m;
    public $relasi_jabatan_m;
    public $question_answer_m;
    public $online_exam_user_answer_m;
    public $online_exam_user_answer_option_m;
    public $online_exam_payment_m;
    public $instruction_m;
    public $upload;
    public $upload_data;
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
        $this->load->model('online_exam_m');
        $this->load->model('online_exam_payment_m');
        $this->load->model('online_exam_question_m');
        $this->load->model('instruction_m');
        $this->load->model('question_bank_m');
        $this->load->model('question_option_m');
        $this->load->model('question_level_report_m');
        $this->load->model('relasi_jabatan_m');
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
        $language = $this->session->userdata('lang');
        $this->lang->load('take_exam', $language);

        // $this->payment_gateway       = new PaymentGateway();
        // $this->payment_gateway_array = pluck($this->payment_gateway_m->get_order_by_payment_gateway(['status' => 1]), 'status', 'slug');
    }

    public function index()
    {
        $this->data['headerassets'] = [
            'css' => [
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css'
            ],
            'js'  => [
                'assets/select2/select2.js'
            ]
        ];

        $usertypeID  = $this->session->userdata('usertypeID');
        $loginuserID = $this->session->userdata('loginuserID');

        $this->data['userSubjectPluck'] = [];
        if ($usertypeID == '3') {
            $this->data['student'] = $this->student_m->get_single_student(['studentID' => $loginuserID]);
            if (inicompute($this->data['student'])) {
                $this->data['userSubjectPluck'] = pluck($this->subject_m->get_order_by_subject([
                    'classesID' => $this->data['student']->classesID,
                    'type'      => 1
                ]), 'subjectID', 'subjectID');
                $optionalSubject                = $this->subject_m->get_single_subject([
                    'type'      => 0,
                    'subjectID' => $this->data['student']->optionalsubjectID
                ]);
                if (inicompute($optionalSubject)) {
                    $this->data['userSubjectPluck'][$optionalSubject->subjectID] = $optionalSubject->subjectID;
                }
            }
        }

        $this->data['payment_settings'] = $this->payment_gateway_m->get_order_by_payment_gateway(['status' => 1]);
        $user_id = $this->session->userdata('loginuserID');
        $this->db->select('*'); // Replace '*' with the specific columns you need
        $this->db->from('student'); // Replace 'users' with your table name
        $this->db->where('studentID', $user_id); // Assuming 'id' is the column name for user IDs
        $query = $this->db->get();

        $dataresultpertanyaan = null; // Initialize variable

        // Fetch the result from the first query
        if ($query->num_rows() > 0) {
            $result = $query->row(); // Get the first row of the result

            // Perform the second query based on parentID
            $this->db->select('*'); // Specify columns you need, or leave '*' to get all
            $this->db->from('online_exam'); // Query the 'online_exam' table
            $this->db->where('organisasiID', $result->parentID); // Filter by 'parentID'
            
            $secondQuery = $this->db->get(); // Execute the query

            // Check if the second query returned results
            if ($secondQuery->num_rows() > 0) {
                $dataresultpertanyaan = $secondQuery->result(); // Store the result as an array of objects
            } else {
                echo "No data found in the 'online_exam' table for parentID: " . $result->parentID;
            }
        } else {
            echo "No user found with ID: " . $user_id;
        }

        $this->data['pertanyaan'] = $dataresultpertanyaan;

        $this->data['payment_options']  = pluck($this->payment_gateway_option_m->get_payment_gateway_option(), 'payment_value', 'payment_option');

        $this->data['payments']         = pluck_multi_array($this->online_exam_payment_m->get_order_by_online_exam_payment([
            'usertypeID' => $this->session->userdata('usertypeID'),
            'userID'     => $this->session->userdata('loginuserID')
        ]), 'obj', 'online_examID');
        $this->data['paindingpayments'] = pluck($this->online_exam_payment_m->get_order_by_online_exam_payment([
            'usertypeID' => $this->session->userdata('usertypeID'),
            'userID'     => $this->session->userdata('loginuserID'),
            'status'     => 0
        ]), 'obj', 'online_examID');
        $this->data['examStatus']       = pluck($this->online_exam_user_status_m->get_order_by_online_exam_user_status(['userID' => $loginuserID]), 'obj', 'onlineExamID');
        $this->data['usertypeID']       = $usertypeID;
        $this->data['onlineExams']      = $this->online_exam_m->get_order_by_online_exam([
            'usertypeID' => $usertypeID,
            'published'  => 1
        ]);
        $this->data['validationErrors']       = [];
        $this->data['validationOnlineExamID'] = 0;
        if ($_POST !== []) {
            $rules = $this->payment_rules($this->input->post('payment_method'));
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == FALSE) {
                $this->data['validationOnlineExamID'] = $this->input->post('onlineExamID');
                $this->data['validationErrors']       = $this->form_validation->error_array();
                $this->data["subview"]                = "online_exam/take_exam/index";
                $this->load->view('_layout_main', $this->data);
            } elseif ($this->input->post('onlineExamID')) {
                $invoice_data = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $this->input->post('onlineExamID')]);
                if (($invoice_data->paid == 1) && ((float)$invoice_data->cost == 0)) {
                    $this->session->set_flashdata('error', 'Exam amount can not be zero');
                    redirect(base_url('take_exam/index'));
                }
                if (($invoice_data->examStatus == 1) && ($invoice_data->paid == 1) && isset($this->data['paindingpayments'][$invoice_data->onlineExamID])) {
                    $this->session->set_flashdata('error', 'This exam price already paid');
                    redirect(base_url('take_exam/index'));
                }
                $this->payment_gateway->gateway($this->input->post('payment_method'))->payment($this->input->post(), $invoice_data);
            } else {
                $this->session->set_flashdata('error', 'Exam does not found');
                redirect(base_url('take_exam/index'));
            }
        } else {
            $this->data["subview"] = "online_exam/take_exam/index";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function show() //done
    {
        $this->data['headerassets'] = [
            'css' => [
                'assets/checkbox/checkbox.css',
                'assets/inilabs/form/fuelux.min.css'
            ]
        ];
        $this->data['footerassets'] = [
            'js' => [
                'assets/inilabs/form/fuelux.min.js'
            ]
        ];

        $userID       = $this->session->userdata("loginuserID");
        $onlineExamID = htmlentities((string) escapeString($this->uri->segment(3)));
        $relasi = $this->uri->segment(4);
        $examGivenStatus     = FALSE;
        $examGivenDataStatus = FALSE;
        $examExpireStatus    = FALSE;
        $examSubjectStatus   = FALSE;

        if ((int)$onlineExamID !== 0) {

            $this->data['student'] = $this->student_m->get_student($userID);
            if (inicompute($this->data['student'])) {
                $array['classesID']      = $this->data['student']->classesID;
                $array['sectionID']      = $this->data['student']->sectionID;
                $array['studentgroupID'] = $this->data['student']->studentgroupID;
                $array['onlineExamID']   = $onlineExamID;
                $online_exam             = $this->online_exam_m->get_online_exam_by_student($array);


                $userExamCheck = $this->online_exam_user_status_m->get_order_by_online_exam_user_status([
                    'userID'       => $userID,
                    'classesID'    => $array['classesID'],
                    'sectionID'    => $array['sectionID'],
                    'onlineExamID' => $onlineExamID
                ]);

                if (inicompute($online_exam)) {
                    $DDonlineExam = $online_exam;

                    if ($DDonlineExam->examTypeNumber == '4') {
                        $presentDate   = strtotime(date('Y-m-d'));
                        $examStartDate = strtotime((string) $DDonlineExam->startDateTime);
                        $examEndDate   = strtotime((string) $DDonlineExam->endDateTime);
                    } elseif ($DDonlineExam->examTypeNumber == '5') {
                        $presentDate   = strtotime(date('Y-m-d H:i:s'));
                        $examStartDate = strtotime((string) $DDonlineExam->startDateTime);
                        $examEndDate   = strtotime((string) $DDonlineExam->endDateTime);
                    }

                    if ($DDonlineExam->examTypeNumber == '4' || $DDonlineExam->examTypeNumber == '5') {
                        if ($presentDate >= $examStartDate && $presentDate <= $examEndDate) {
                            $examGivenStatus = TRUE;
                        } elseif ($presentDate > $examStartDate && $presentDate > $examEndDate) {
                            $examExpireStatus = TRUE;
                        }
                    } else {
                        $examGivenStatus = TRUE;
                    }

                    if ($examGivenStatus) {
                        $examGivenStatus = FALSE;
                        if ($DDonlineExam->examStatus == 2) {
                            $examGivenStatus = TRUE;
                        } else {
                            $userExamCheck = pluck($userExamCheck, 'obj', 'onlineExamID');
                            if (isset($userExamCheck[$DDonlineExam->onlineExamID])) {
                                $examGivenDataStatus = TRUE;
                            } else {
                                $examGivenStatus = TRUE;
                            }
                        }
                    }

                    if ($examGivenStatus) {
                        if ((int)$DDonlineExam->subjectID && (int)$DDonlineExam->classID) {
                            $examGivenStatus  = FALSE;
                            $userSubjectPluck = pluck($this->subject_m->get_order_by_subject(['type' => 1]), 'subjectID', 'subjectID');
                            $optionalSubject  = $this->subject_m->get_single_subject([
                                'type'      => 0,
                                'subjectID' => $this->data['student']->optionalsubjectID
                            ]);
                            if (inicompute($optionalSubject)) {
                                $userSubjectPluck[$optionalSubject->subjectID] = $optionalSubject->subjectID;
                            }

                            if (in_array($DDonlineExam->subjectID, $userSubjectPluck)) {
                                $examGivenStatus = TRUE;
                            } else {
                                $examSubjectStatus = FALSE;
                            }
                        } else {
                            $examSubjectStatus = TRUE;
                        }
                    } else {
                        $examSubjectStatus = TRUE;
                    }
                }

                $this->data['class'] = $this->classes_m->get_classes($this->data['student']->classesID);
            } else {
                $this->data['class'] = [];
            }

            if (inicompute($this->data['student'])) {
                $this->data['section'] = $this->section_m->get_section($this->data['student']->sectionID);
            } else {
                $this->data['section'] = [];
            }

            $this->data['onlineExam'] = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
            if (inicompute($online_exam)) {
                $onlineExamQuestions = $this->online_exam_question_m->get_order_by_online_exam_question(['onlineExamID' => $onlineExamID]);

                $allOnlineExamQuestions = $onlineExamQuestions;
                $this->db->select('*');
                $this->db->from('question_level');
                $query = $this->db->get();
                $result = $query->result();
                $result;
                if ($this->data['onlineExam']->random == 1) {
                    $this->db->from('online_exam_question')->where(['onlineExamID' => $onlineExamID])->order_by('', define('RANDOM',true));
                    $query = $this->db->get();
                    $onlineExamQuestions = $query->result();
                    $allOnlineExamQuestions = $onlineExamQuestions;
                }

                $this->data['onlineExamQuestions'] = $onlineExamQuestions;
                $onlineExamQuestions               = pluck($onlineExamQuestions, 'obj', 'questionID');
                $questionsBank                     = pluck($this->question_bank_m->get_where_in(array_keys($onlineExamQuestions), 'questionBankID'), 'obj', 'questionBankID');
                $this->data['questions']           = $questionsBank;

                // Initialize the new array to hold the results (as an array of objects)
                $newArray = [];

                // Loop through each result
                foreach ($result as $res) {
                    // Initialize a new stdClass object with result data
                    $entry = new stdClass();
                    $entry->idresult = $res->questionLevelID;
                    $entry->nameresult = $res->name;
                    $entry->detail_soal = [];
                    
                    // Loop through each question in the questions bank
                    foreach ($questionsBank as $question) {
                        // Check if the levelID matches the questionLevelID
                        if ($question->levelID == $res->questionLevelID) {
                            // Add the question to the 'detail_soal' array
                            $entry->detail_soal[] = $question;
                        }
                    }

                    // Add the stdClass object to the new array
                    if(!empty($entry->detail_soal)){
                        $newArray[] = $entry;
                    }
                }

                $this->data['newArray'] = $newArray;

                $options    = [];
                $answers    = [];
                $allOptions = [];
                $allAnswers = [];
                if (inicompute($allOnlineExamQuestions)) {
                    $pluckOnlineExamQuestions = pluck($allOnlineExamQuestions, 'questionID');
                    $allOptions               = $this->question_option_m->get_where_in_question_option($pluckOnlineExamQuestions, 'questionID');
                    foreach ($allOptions as $option) {
                        if ($option->name == "" && $option->img == "")
                            continue;
                        $options[$option->questionID][] = $option;
                    }
                    $this->data['options'] = $options;

                    $allAnswers = $this->question_answer_m->get_where_in_question_answer($pluckOnlineExamQuestions, 'questionID');
                    foreach ($allAnswers as $answer) {
                        $answers[$answer->questionID][] = $answer;
                    }
                    $this->data['answers'] = $answers;
                } else {
                    $this->data['options'] = $options;
                    $this->data['answers'] = $answers;
                }


                /**
                 * STORE DATA
                 */
                if ($_POST !== []) {
                    // Start transaction
                    $this->db->trans_begin();

                    try {
                        $time               = date("Y-m-d h:i:s");
                        $mainQuestionAnswer = [];
                        $typeNumber         = null;
                        $userAnswer         = $this->input->post('answer');
                        $fileAnswer         = [];
                        if(!empty($_FILES)){
                            $fileAnswer = $_FILES['file'];
                        }
                        // dd($userAnswer);

                        $questionStatus    = [];
                        $correctAnswer     = 0;
                        $totalQuestionMark = 0;
                        $totalCorrectMark  = 0;
                        $visited           = [];
                        foreach ($allAnswers as $answer) {
                            if ($answer->typeNumber == 3) {
                                $mainQuestionAnswer[$answer->typeNumber][$answer->questionID][$answer->answerID] = $answer->text;
                            }else if($answer->typeNumber == 4){
                                $mainQuestionAnswer[$answer->typeNumber][$answer->questionID][] = $answer->nilaijawaban;
                            }else if($answer->typeNumber == 5){
                                $mainQuestionAnswer[$answer->typeNumber][$answer->questionID][] = $answer->text;
                            }else {
                                $mainQuestionAnswer[$answer->typeNumber][$answer->questionID][] = $answer->optionID;
                            }
                        }

                        $totalAnswer = 0;
                        if (inicompute($userAnswer)) {
                            foreach ($userAnswer as $userAnswerKey => $uA) {
                                $totalAnswer += inicompute($uA);
                            }
                        }


                        if (inicompute($allOnlineExamQuestions)) {
                            foreach ($allOnlineExamQuestions as $aoeq) {
                                if (isset($questionsBank[$aoeq->questionID])) {
                                    if($questionsBank[$aoeq->questionID]->typeNumber == 4){
                                        $totalQuestionMark -= $questionsBank[$aoeq->questionID]->mark;
                                    } else if($questionsBank[$aoeq->questionID]->typeNumber == 5){
                                        $totalQuestionMark -= $questionsBank[$aoeq->questionID]->mark;
                                    }
                                    $totalQuestionMark += $questionsBank[$aoeq->questionID]->mark;
                                }
                            }
                        }

                        $f        = 0;

                        $examtime =  $this->db->select('examtimeID')->from('online_exam_user_status')
                            ->where([
                                'userID'       => $userID,
                                'onlineExamID' => $onlineExamID
                            ])
                            ->limit('1')
                            ->order_by('onlineExamUserStatus', 'DESC')
                            ->get()->row();

                        $examTimeCounter = 1;
                        if (inicompute($examtime)) {
                            $examTimeCounter = $examtime->examtimeID;
                            $examTimeCounter++;
                        }
                        $this->data['attemptedID']     = $examTimeCounter;

                        $statusID = 10;
                        $user_options=[];
                        $user_answer =[];
                        foreach ($mainQuestionAnswer as $typeID => $questions) {
                            if (!isset($userAnswer[$typeID]))
                                continue;
                            foreach ($questions as $questionID => $options) {
                                if (isset($onlineExamQuestions[$questionID])) {
                                    $onlineExamQuestionID   = $onlineExamQuestions[$questionID]->onlineExamQuestionID;
                                    $this->online_exam_user_answer_m->insert([
                                        'onlineExamQuestionID' => $onlineExamQuestionID,
                                        'userID'               => $userID,
                                        'onlineExamID'         => $onlineExamID,
                                        'examtimeID'           => $examTimeCounter,
                                        'relasi_jabatan'       => $relasi,
                                    ]);
                                    $user_answer[] = $this->db->insert_id();
                                }
                                if (isset($userAnswer[$typeID][$questionID])) {
                                    $totalCorrectMark += isset($questionsBank[$questionID]) ? $questionsBank[$questionID]->mark : 0;

                                    $questionStatus[$questionID] = 1;
                                    $correctAnswer++;
                                    $f = 1;
                                    if ($typeID == 3) {
                                        foreach ($options as $answerID => $answer) {
                                            $takeAnswer = strtolower((string) $answer);
                                            $getAnswer  = isset($userAnswer[$typeID][$questionID][$answerID]) ? strtolower((string) $userAnswer[$typeID][$questionID][$answerID]) : '';
                                            $this->online_exam_user_answer_option_m->insert([
                                                'questionID'   => $questionID,
                                                'typeID'       => $typeID,
                                                'text'         => $getAnswer,
                                                'time'         => $time,
                                                'onlineExamID' => $onlineExamID,
                                                'examtimeID'   => $examTimeCounter,
                                                'userID'       => $userID,
                                                'relasi_jabatan'       => $relasi,
                                            ]);
                                            if ($getAnswer !== $takeAnswer) {
                                                $f = 0;
                                            }
                                        }
                                    } elseif ($typeID == 1 || $typeID == 2) {
                                        if (inicompute($options) != inicompute($userAnswer[$typeID][$questionID])) {
                                            $f = 0;
                                        } else {
                                            if (!isset($visited[$typeID][$questionID])) {
                                                foreach ($userAnswer[$typeID][$questionID] as $userOption) {
                                                    $this->online_exam_user_answer_option_m->insert([
                                                        'questionID'   => $questionID,
                                                        'optionID'     => $userOption,
                                                        'typeID'       => $typeID,
                                                        'time'         => $time,
                                                        'onlineExamID' => $onlineExamID,
                                                        'examtimeID'   => $examTimeCounter,
                                                        'userID'       => $userID,
                                                    'relasi_jabatan'       => $relasi,
                                                    ]);
                                                }
                                                $visited[$typeID][$questionID] = 1;
                                            }
                                            foreach ($options as $answerID => $answer) {
                                                if (!in_array($answer, $userAnswer[$typeID][$questionID])) {
                                                    $f = 0;
                                                    break;
                                                }
                                            }
                                        }
                                    } elseif ($typeID == 4) {
                                        $data = $this->question_option_m->get_answer_by_id($userAnswer[$typeID][$questionID][0]);
                                        $totalQuestionMark += $data->nilaijawaban;
                                        $correctAnswer++;
                                        if (!isset($visited[$typeID][$questionID])) {
                                            foreach ($userAnswer[$typeID][$questionID] as $userOption) {
                                                $this->online_exam_user_answer_option_m->insert([
                                                    'questionID'   => $questionID,
                                                    'optionID'     => $userOption,
                                                    'typeID'       => $typeID,
                                                    'time'         => $time,
                                                    'onlineExamID' => $onlineExamID,
                                                    'examtimeID'   => $examTimeCounter,
                                                    'userID'       => $userID,
                                                    'relasi_jabatan'       => $relasi,
                                                ]);
                                            }
                                            $visited[$typeID][$questionID] = 1;
                                        }
                                        foreach ($options as $answerID => $answer) {
                                            if (!in_array($answer, $userAnswer[$typeID][$questionID])) {
                                                $f = 0;
                                                break;
                                            }
                                        }
                                    } elseif ($typeID == 5) {
                                        foreach ($options as $answerID => $answer) {
                                            $new_file = '';
                                            if ($fileAnswer['name'][$typeID][$questionID] != "") {
                                                $path = "./uploads/files/";
                                                if (!is_dir($path)) {
                                                    mkdir($path, 0755, true); // Create directory with 0755 permissions and recursive flag set to true
                                                }
                                                $file_name = $fileAnswer['name'][$typeID][$questionID];
                                                $random = random19();
                                                $makeRandom = hash('sha512', $random.$fileAnswer['name'][$typeID][$questionID].date('Y-M-d-H:i:s') . config_item("encryption_key"));
                                                $file_name_rename = $makeRandom;
                                                $explode = explode('.', (string) $file_name);
                                                if(inicompute($explode) >= 2) {
                                                    $new_file = $file_name_rename.'.'.end($explode);
                                                    $config['upload_path'] = $path;
                                                    $config['allowed_types'] = "pdf|jpg|jpeg|png";
                                                    $config['file_name'] = $new_file;
                                                    $config['max_size'] = (1024*20);
                                                    $config['max_width'] = '3000';
                                                    $config['max_height'] = '3000';
                                                    $this->load->library('upload');
                                                    $this->upload->initialize($config);
    
                                                    // Manually set the file data for this specific file
                                                    $_FILES['single_file']['name'] = $fileAnswer['name'][$typeID][$questionID];
                                                    $_FILES['single_file']['type'] = $fileAnswer['type'][$typeID][$questionID];
                                                    $_FILES['single_file']['tmp_name'] = $fileAnswer['tmp_name'][$typeID][$questionID];
                                                    $_FILES['single_file']['error'] = $fileAnswer['error'][$typeID][$questionID];
                                                    $_FILES['single_file']['size'] = $fileAnswer['size'][$typeID][$questionID];
    
                                                    if(!$this->upload->do_upload("single_file")) {
                                                        log_message('error', $this->upload->display_errors()); // Log the error message for debugging
                                                        $this->form_validation->set_message("fileupload", $this->upload->display_errors());
                                                    }else{
                                                        $this->upload_data[$typeID][$questionID] =  $this->upload->data();
                                                    } 
                                                }
                                            }

                                            // save jawaban
                                            $takeAnswer = strtolower((string) $answer);
                                            $getAnswer  = isset($userAnswer[$typeID][$questionID]) ? strtolower((string) $userAnswer[$typeID][$questionID]) : '';
                                            $insert_online_exam_user_answer_option_m = $this->online_exam_user_answer_option_m->insert([
                                                'questionID'           => $questionID,
                                                'typeID'               => $typeID,
                                                'text'                 => $getAnswer,
                                                'time'                 => $time,
                                                'onlineExamID'         => $onlineExamID,
                                                'examtimeID'           => $examTimeCounter,
                                                'userID'               => $userID,
                                                'relasi_jabatan'       => $relasi,
                                                'fileAnswer'           => $new_file != '' ? $path.$new_file : null
                                            ]);
                                            $user_options[] = $this->db->insert_id();
                                        }
                                    }

                                    if ($f === 0) {
                                        $questionStatus[$questionID] = 0;
                                        $correctAnswer--;
                                        $totalCorrectMark -= $questionsBank[$questionID]->mark;
                                    }
                                }
                            }
                        }
                        
                        if (inicompute($this->data['onlineExam'])) {
                            if ($this->data['onlineExam']->markType == 5) {

                                $percentage = 0;
                                if ($totalCorrectMark > 0 && $totalQuestionMark > 0) {
                                    $percentage = (($totalCorrectMark / $totalQuestionMark) * 100);
                                }

                                $statusID = $percentage >= $this->data['onlineExam']->percentage ? 5 : 10;
                            } elseif ($this->data['onlineExam']->markType == 10) {
                                $statusID = $totalCorrectMark >= $this->data['onlineExam']->percentage ? 5 : 10;
                            }
                        }

                        $this->online_exam_user_status_m->insert([
                            'onlineExamID'       => $this->data['onlineExam']->onlineExamID,
                            'time'               => $time,
                            'totalQuestion'      => inicompute($onlineExamQuestions),
                            'totalAnswer'        => $totalAnswer,
                            'nagetiveMark'       => $this->data['onlineExam']->negativeMark,
                            'duration'           => $this->data['onlineExam']->duration,
                            'score'              => $correctAnswer,
                            'userID'             => $userID,
                            'classesID'          => inicompute($this->data['class']) ? $this->data['class']->classesID : 0,
                            'sectionID'          => inicompute($this->data['section']) ? $this->data['section']->sectionID : 0,
                            'examtimeID'         => $examTimeCounter,
                            'totalCurrectAnswer' => $correctAnswer,
                            'totalMark'          => $totalQuestionMark,
                            'totalObtainedMark'  => $totalCorrectMark,
                            'totalPercentage'    => (($totalCorrectMark > 0 && $totalQuestionMark > 0) ? (($totalCorrectMark / $totalQuestionMark) * 100) : 0),
                            'statusID'           => $statusID,
                            'relasi_jabatan'       => $relasi,
                        ]);

                        if ($this->data['onlineExam']->paid) {
                            $onlineExamPayments = $this->online_exam_payment_m->get_single_online_exam_payment_only_first_row([
                                'online_examID' => $this->data['onlineExam']->onlineExamID,
                                'status'        => 0,
                                'usertypeID'    => $this->session->userdata('usertypeID'),
                                'userID'        => $this->session->userdata('loginuserID')
                            ]);

                            if ($onlineExamPayments->online_exam_paymentID != NULL) {
                                $onlineExamPaymentArray = [
                                    'status' => 1
                                ];
                                $this->online_exam_payment_m->update_online_exam_payment($onlineExamPaymentArray, $onlineExamPayments->online_exam_paymentID);
                            }
                        }

                        $allUserExams = $this->online_exam_user_status_m->get_online_exam_user_status();
                        $givenTimes = [];
                        $allExams = pluck($this->online_exam_m->get_online_exam(), 'showMarkAfterExam', 'onlineExamID');
                        foreach ($allUserExams as $allUserExam) {
                            if (!array_key_exists($allUserExam->onlineExamID, $givenTimes)) {
                                $givenTimes[$allUserExam->onlineExamID] = $allExams[$allUserExam->onlineExamID];
                            }
                        }

                        $resultAnswer = $this->question_level_report_m->compute_jawaban($userID,$relasi,$this->data['onlineExam']->onlineExamID, $user_options, $user_answer);
                        $this->question_level_report_m->insert_batch_question_level_report($resultAnswer);
                        $this->data['showResult']        = $givenTimes;
                        $this->data['fail']              = $f;
                        $this->data['questionStatus']    = $questionStatus;
                        $this->data['totalAnswer']       = $totalAnswer;
                        $this->data['correctAnswer']     = $correctAnswer;
                        $this->data['totalCorrectMark']  = $totalCorrectMark;
                        $this->data['totalQuestionMark'] = $totalQuestionMark;
                        $this->data['userExamCheck']     = $userExamCheck;
                        $this->data['onlineExamID']      = $onlineExamID;
                        $this->data["subview"]           = "online_exam/take_exam/result";

                        if ($this->db->trans_status() === FALSE) {
                            $this->db->trans_rollback();
                            $this->data["subview"] = "error";
                            return $this->load->view('_layout_main', $this->data);
                        }else{
                            $this->db->trans_commit();
                        }
                        return $this->load->view('_layout_main', $this->data);

                    } catch (Exception $e) {
                        // Catch any exceptions and rollback the transaction
                        $this->db->trans_rollback();
                        log_message('error', $e->getMessage()); // Log the error message for debugging
                        $this->data["subview"] = "error";
                        return $this->load->view('_layout_main', $this->data);
                    }
                }

                /** */

                if ($examGivenStatus || $userExamCheck[$onlineExamID]->relasi_jabatan != $relasi) {
                    $this->data["subview"] = "online_exam/take_exam/question";
                    return $this->load->view('_layout_main', $this->data);
                } elseif ($examGivenDataStatus) {
                    $this->data['online_exam']   = $online_exam;
                    $userExamCheck               = pluck($userExamCheck, 'obj', 'onlineExamID');
                    $this->data['userExamCheck'] = isset($userExamCheck[$onlineExamID]) ? $userExamCheck[$onlineExamID] : [];
                    $this->data["subview"]       = "online_exam/take_exam/checkexam";
                    return $this->load->view('_layout_main', $this->data);
                } elseif ($examExpireStatus) {
                    $this->data['examsubjectstatus'] = $examSubjectStatus;
                    $this->data['expirestatus']      = $examExpireStatus;
                    $this->data['upcomingstatus']    = FALSE;
                    $this->data['online_exam']       = $online_exam;
                    $this->data["subview"]           = "online_exam/take_exam/expireandupcoming";
                    return $this->load->view('_layout_main', $this->data);
                } else {
                    $this->data['examsubjectstatus'] = $examSubjectStatus;
                    $this->data['expirestatus']      = $examExpireStatus;
                    $this->data['upcomingstatus']    = TRUE;
                    $this->data['online_exam']       = $online_exam;
                    $this->data["subview"]           = "online_exam/take_exam/expireandupcoming";
                    return $this->load->view('_layout_main', $this->data);
                }
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function getAnswerList()
    {
        $useranswer             = array();
        $examans                = array();
        $fillintheblankUserAns  = array();
        $fillintheExamAns       = array();

        $onlineExamID = htmlentities((string) escapeString($this->uri->segment(3)));
        $attemptID    = htmlentities((string) escapeString($this->uri->segment(4)));
        $studentID    = htmlentities((string) escapeString($this->uri->segment(5)));

        $this->data['onlineExamID'] = $onlineExamID;
        $this->data['studentID']    = $studentID;
        $this->data['attemptID']    = $attemptID;
        $this->data['typeNumber']   = 2;
        $this->data['exam']         = $this->online_exam_m->get_single_online_exam(array('onlineExamID' => $onlineExamID));
        if (inicompute($this->data['exam'])) {
            $this->data['typeName'] = $this->lang->line('take_exam_question');
            $array = [];
            if ((int)$onlineExamID && $onlineExamID > 0) {
                $array['onlineExamID'] = $onlineExamID;
            }
            if ((int)$studentID && $studentID > 0) {
                $array['userID'] = $studentID;
            }
            if ((int)$attemptID && $attemptID > 0) {
                $array['examtimeID'] = $attemptID;
            }

            $examquestions = pluck($this->online_exam_question_m->get_order_by_online_exam_question(array('onlineExamID' => $onlineExamID)), 'questionID');
            $examquestionsuseranswer = $this->online_exam_user_answer_option_m->get_order_by_online_exam_user_answer_option($array);
            if(inicompute($examquestionsuseranswer)){
                foreach($examquestionsuseranswer as $userquestionans){
                    $useranswer[$userquestionans->optionID][$userquestionans->questionID] = $userquestionans;
                    $fillintheblankUserAns[$userquestionans->text][$userquestionans->questionID][$userquestionans->typeID] = $userquestionans;
                }
            }
            $examquestionsanswer =  $this->question_answer_m->get_question_answerArray($examquestions, 'questionID');
            if(inicompute($examquestionsanswer)){
                foreach($examquestionsanswer as $ans){
                    $examans[$ans->optionID][$ans->questionID] = $ans;
                    $fillintheExamAns[$ans->text][$ans->questionID][$ans->typeNumber] =  $ans;
                }
            }

            $this->data['questions']                = pluck($this->question_bank_m->get_question_bank_questionArray($examquestions, 'questionBankID'), 'obj', 'questionBankID');
            $this->data['question_options']         = pluck_multi_array($this->question_option_m->get_question_option_by_questionArray($examquestions, 'questionID'), 'obj', 'questionID');
            $this->data['examquestionsuseranswer']  = $useranswer;
            $this->data['examquestionsanswer']      = $examans;
            $this->data['fillintheblankUserAns']    = $fillintheblankUserAns;
            $this->data['fillintheExamAns']         = $fillintheExamAns;
            $this->data['question_answer_options']  = pluck_multi_array($this->question_answer_m->get_order_by_question_answer(), 'obj', 'questionID');
            $this->data['onlineExamUserAnsOption']  = pluck_multi_array($this->online_exam_user_answer_option_m->get_order_by_online_exam_user_answer_option($array),'obj', 'questionID');
            $this->data["subview"]                  = "online_exam/take_exam/examanswer";
            $this->load->view('_layout_main', $this->data);
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function instruction() //done
    {
        $onlineExamID = htmlentities((string) escapeString($this->uri->segment(3)));
        $atasan = $this->uri->segment(4);
        if ((int)$onlineExamID !== 0) {
            $instructions             = pluck($this->instruction_m->get_order_by_instruction(), 'obj', 'instructionID');
            $onlineExam               = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
            $this->data['onlineExam'] = $onlineExam;
            if (!isset($instructions[$onlineExam->instructionID])) {
                redirect(base_url('take_exam/show/' . $onlineExamID.'/'.$atasan));
            }
            $this->data['instruction'] = $instructions[$onlineExam->instructionID];
            $this->data["subview"]     = "online_exam/take_exam/instruction";
            return $this->load->view('_layout_main', $this->data);
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function randAssociativeArray($array, $number = 0)
    {
        $returnArray = [];
        $countArray  = inicompute($array);
        if ($number > $countArray || $number == 0) {
            $number = $countArray;
        }

        if ($countArray == 1) {
            $randomKey[] = 0;
        } elseif (inicompute($array)) {
            $randomKey = array_rand($array, $number);
        } else {
            $randomKey = [];
        }

        if (is_array($randomKey)) {
            shuffle($randomKey);
        }

        if (inicompute($randomKey)) {
            foreach ($randomKey as $key) {
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
        if (permissionChecker('take_exam') && (!empty($onlineExamID) && (int)$onlineExamID && $onlineExamID > 0)) {
            $onlineExam = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
            if (inicompute($onlineExam)) {
                $retArray['status']        = true;
                $retArray['payableamount'] = sprintf("%.2f", $onlineExam->cost);
            }
        }

        echo json_encode($retArray);
        exit;
    }

    public function payment_list() //done
    {
        $onlineExamID = $this->input->post('onlineExamID');
        if (!empty($onlineExamID) && (int)$onlineExamID && $onlineExamID > 0) {
            $onlineExam = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
            if (inicompute($onlineExam)) {
                $onlineExamPayments = $this->online_exam_payment_m->get_order_by_online_exam_payment([
                    'online_examID' => $onlineExamID,
                    'usertypeID'    => $this->session->userdata('usertypeID'),
                    'userID'        => $this->session->userdata('loginuserID')
                ]);
                if (inicompute($onlineExamPayments)) {
                    $i = 1;
                    foreach ($onlineExamPayments as $onlineExamPayment) {
                        echo '<tr>';
                        echo '<td data-title="' . $this->lang->line('slno') . '">';
                        echo $i;
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('take_exam_payment_date') . '">';
                        echo date('d M Y', strtotime((string) $onlineExamPayment->paymentdate));
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('take_exam_payment_method') . '">';
                        echo $onlineExamPayment->paymentmethod;
                        echo '</td>';

                        echo '<td data-title="' . $this->lang->line('take_exam_exam_status') . '">';
                        if ($onlineExamPayment->status) {
                            echo $this->lang->line('take_exam_complete');
                        } else {
                            echo $this->lang->line('take_exam_pending');
                        }
                        echo '</td>';
                        echo '</tr>';
                    }
                }
            }
        }
    }

    protected function payment_rules($method): array //done
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

    public function unique_payment_method(): bool  //done
    {
        if ($this->input->post('payment_method') === 'select') {
            $this->form_validation->set_message("unique_payment_method", "Payment method is required.");
            return false;
        } else {
            if (!$this->payment_gateway->gateway($this->input->post('payment_method'))->status()) {
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
        if ($onlineExamID > 0) {
            $onlineExam = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
            if (inicompute($onlineExam) && (($onlineExam->examStatus == 2) && ($onlineExam->paid == 1))) {
                if ($onlineExam->examTypeNumber == '4') {
                    $presentDate   = strtotime(date('Y-m-d'));
                    $examStartDate = strtotime((string) $onlineExam->startDateTime);
                    $examEndDate   = strtotime((string) $onlineExam->endDateTime);
                } elseif ($onlineExam->examTypeNumber == '5') {
                    $presentDate   = strtotime(date('Y-m-d H:i:s'));
                    $examStartDate = strtotime((string) $onlineExam->startDateTime);
                    $examEndDate   = strtotime((string) $onlineExam->endDateTime);
                }
                if (($onlineExam->examTypeNumber == '4' || $onlineExam->examTypeNumber == '5') && ($presentDate > $examStartDate && $presentDate > $examEndDate)) {
                    $paymentExpireStatus = FALSE;
                }
                if ($paymentExpireStatus) {
                    $onlineExamPayments = $this->online_exam_payment_m->get_single_online_exam_payment_only_first_row([
                        'online_examID' => $onlineExamID,
                        'status'        => 0,
                        'usertypeID'    => $this->session->userdata('usertypeID'),
                        'userID'        => $this->session->userdata('loginuserID')
                    ]);
                    if ($onlineExamPayments->online_exam_paymentID == NULL) {
                        $status = 'TRUE';
                    }
                }
            }
        }

        echo $status;
    }

    public function success()
    {
        if (isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->success();
        }
    }

    public function cancel()
    {
        if (isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->cancel();
        }
    }

    public function fail()
    {
        if (isset($this->payment_gateway_array[htmlentities((string) escapeString($this->uri->segment(3)))])) {
            $this->payment_gateway->gateway(htmlentities((string) escapeString($this->uri->segment(3))))->fail();
        }
    }
}

