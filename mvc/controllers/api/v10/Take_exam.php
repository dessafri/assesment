<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

require_once(APPPATH . 'libraries/PaymentGateway/PaymentGateway.php');

class Take_exam extends Api_Controller
{

    public $load;
    public $session;
    public $uri;
    public $student_m;
    public $online_exam_m;
    public $online_exam_user_status_m;
    public $subject_m;
    public $online_exam_question_m;
    public $db;
    public $question_bank_m;
    public $retdata;
    public $question_option_m;
    public $question_answer_m;
    public $online_exam_user_answer_m;
    public $online_exam_user_answer_option_m;
    public $online_exam_payment_m;
    public $instruction_m;
    public $data;
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

        $this->payment_gateway       = new PaymentGateway();
        $this->payment_gateway_array = pluck($this->payment_gateway_m->get_order_by_payment_gateway(['status' => 1]), 'status', 'slug');
    }





    public function exam_get() //done
    {
        $userID       = $this->session->userdata("loginuserID");
        $onlineExamID = htmlentities((string) escapeString($this->uri->segment(5)));
        $examGivenStatus     = FALSE;
        $examGivenDataStatus = FALSE;
        $examExpireStatus    = FALSE;
        $examUpcomingStatus    = FALSE;
        $examSubjectStatus   = FALSE;

        if ((int)$onlineExamID !== 0) {
            $student = $this->student_m->get_student($userID);
            if (inicompute($student)) {
                $array['classesID']      = $student->classesID;
                $array['sectionID']      = $student->sectionID;
                $array['studentgroupID'] = $student->studentgroupID;
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

                    if ($DDonlineExam->examTypeNumber == '4' || $DDonlineExam->examTypeNumber == '3') {
                        $presentDate   = strtotime(date('Y-m-d'));
                        $examStartDate = strtotime((string) $DDonlineExam->startDateTime);
                        $examEndDate   = strtotime((string) $DDonlineExam->endDateTime);
                    } elseif ($DDonlineExam->examTypeNumber == '5') {
                        $presentDate   = strtotime(date('Y-m-d H:i:s'));
                        $examStartDate = strtotime((string) $DDonlineExam->startDateTime);
                        $examEndDate   = strtotime((string) $DDonlineExam->endDateTime);
                    }

                    if ($DDonlineExam->examTypeNumber == '4' || $DDonlineExam->examTypeNumber == '5' || $DDonlineExam->examTypeNumber == '3') {
                        if ($presentDate >= $examStartDate && $presentDate <= $examEndDate) {
                            $examGivenStatus = TRUE;
                        } elseif ($presentDate > $examStartDate && $presentDate > $examEndDate) {
                            $examExpireStatus = TRUE;
                        } else {
                            $examUpcomingStatus = TRUE;
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
                                'subjectID' => $student->optionalsubjectID
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
            }


            if (inicompute($online_exam)) {
                $onlineExamQuestions = $this->online_exam_question_m->get_order_by_online_exam_question(['onlineExamID' => $onlineExamID]);

                $allOnlineExamQuestions = $onlineExamQuestions;

                if ($online_exam->random == 1) {
                    $this->db->from('online_exam_question')->where(['onlineExamID' => $onlineExamID])->order_by('', RANDOM);
                    $query = $this->db->get();
                    $onlineExamQuestions = $query->result();
                    $allOnlineExamQuestions = $onlineExamQuestions;
                }

                $availableQuestions = [];

                foreach ($allOnlineExamQuestions as $question) {
                    $availableQuestions[] = $question->questionID;
                }
                $questionsBanks                     = pluck($this->question_bank_m->get_order_by_question_bank(), 'obj', 'questionBankID');
                $examQuestions = [];
                $i = 0;
                foreach ($questionsBanks as  $question) {
                    if (in_array($question->questionBankID, $availableQuestions)) {
                        $examQuestions[$i]['question'] = strip_tags((string) $question->question);
                        $examQuestions[$i]['questionBankID'] = $question->questionBankID;
                        $examQuestions[$i]['explanation'] = $question->explanation;
                        $examQuestions[$i]['totalOption'] = $question->totalOption;
                        $examQuestions[$i]['typeNumber'] = $question->typeNumber;
                        $examQuestions[$i]['hints'] = $question->hints;
                        $examQuestions[$i]['image'] = empty($question->upload) ? null :  imagelink($question->upload);
                        $i++;
                    }
                }

                $onlineExamQuestions               = pluck($onlineExamQuestions, 'obj', 'questionID');

                $this->retdata['questions'] = $examQuestions;
                $options    = [];
                $allOptions = [];
                if (inicompute($allOnlineExamQuestions) && inicompute($examQuestions)) {
                    $pluckOnlineExamQuestions = pluck($allOnlineExamQuestions, 'questionID');
                    $allOptions               = $this->question_option_m->get_where_in_question_option($pluckOnlineExamQuestions, 'questionID');

                    foreach ($examQuestions as $examQuestion) {
                        $j = 0;
                        foreach ($allOptions as $option) {

                            if ($examQuestion['questionBankID'] == $option->questionID) {
                                if ($option->name == "" && $option->img == "")
                                    continue;
                                $options[$option->questionID][$j]['optionID'] = $option->optionID;
                                $options[$option->questionID][$j]['questionID'] = $option->questionID;
                                $options[$option->questionID][$j]['name'] = $option->name;
                                $options[$option->questionID][$j]['image'] = $option->img == "" ? null : imagelink($option->img);
                                $j++;
                            }
                        }
                    }

                    $this->retdata['options'] = $options;
                } else {
                    $this->retdata['options'] = $options;
                }
                if ($examGivenStatus) {
                    $this->response([
                        'status'    => true,
                        'message'   => 'Already Given Once',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                } elseif ($examGivenDataStatus) {
                    $userExamCheck               = pluck($userExamCheck, 'obj', 'onlineExamID');
                    $this->retdata['userExamCheck'] = isset($userExamCheck[$onlineExamID]) ? $userExamCheck[$onlineExamID] : [];
                    $this->response([
                        'status'    => true,
                        'message'   => 'Success',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                } elseif ($examExpireStatus) {
                    $this->retdata['examsubjectstatus'] = $examSubjectStatus;
                    $this->retdata['expirestatus']      = $examExpireStatus;
                    $this->retdata['upcomingstatus']    = FALSE;
                    if ($online_exam->examTypeNumber == 4) {
                        $this->retdata['startDate']    = date('d M Y', strtotime((string) $online_exam->startDateTime));
                        $this->retdata['endDate']    = date('d M Y', strtotime((string) $online_exam->endDateTime));
                    }
                    if ($online_exam->examTypeNumber == 5) {
                        $this->retdata['startDate']    = date('d M Y', strtotime((string) $online_exam->startDateTime));
                        $this->retdata['endDate']    = date('d M Y', strtotime((string) $online_exam->endDateTime));
                        $this->retdata['startTime']    =  date('h:i a', strtotime((string) $online_exam->startDateTime));
                    }
                    $this->retdata['duration']    = $online_exam->duration;
                    $this->response([
                        'status'    => true,
                        'message'   => 'Exam Expired',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                } elseif ($examUpcomingStatus) {
                    if ($online_exam->examTypeNumber == 4) {
                        $this->retdata['startDate']    = date('d M Y', strtotime((string) $online_exam->startDateTime));
                        $this->retdata['endDate']    = date('d M Y', strtotime((string) $online_exam->endDateTime));
                    }
                    if ($online_exam->examTypeNumber == 5) {
                        $this->retdata['startDate']    = date('d M Y', strtotime((string) $online_exam->startDateTime));
                        $this->retdata['endDate']    = date('d M Y', strtotime((string) $online_exam->endDateTime));
                        $this->retdata['startTime']    =  date('h:i a', strtotime((string) $online_exam->startDateTime));
                    }
                    $this->retdata['duration']    = $online_exam->duration;
                    $this->retdata['examsubjectstatus'] = $examSubjectStatus;
                    $this->retdata['upcomingstatus']    = TRUE;

                    $this->response([
                        'status'    => true,
                        'message'   => 'Upcoming',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->retdata['examsubjectstatus'] = $examSubjectStatus;
                    $this->retdata['upcomingstatus']    = TRUE;
                    $this->response([
                        'status'    => true,
                        'message'   => 'Success',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                }
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Error 404',
                    'data' => $this->retdata
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => $this->retdata
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function submit_post()
    {
        $time               = date("Y-m-d h:i:s");
        $mainQuestionAnswer = [];
        $array = [];
        $allAnswers = [];
        $inputAnswers = json_decode((string) inputCall('answer'), true);
        $userAnswers = [];
        $onlineExamID = 0;
        foreach ($inputAnswers as $answer) {

            $answer = (object)$answer;
            $onlineExamID = $answer->examID;
            if (!empty($answer->questionBankID)) {
                if ($answer->typeNumber == 3) {
                    $userAnswers[$answer->typeNumber][$answer->questionBankID][$answer->questionBankID] = $answer->optionText;
                } else {
                    $userAnswers[$answer->typeNumber][$answer->questionBankID][] = $answer->optionID;
                }
            }
        }
        $userAnswer         = $userAnswers;

        $userID       = $this->session->userdata("loginuserID");
        if ((int)$onlineExamID !== 0) {
            $student = $this->student_m->get_student($userID);
            if (inicompute($student)) {
                $array['classesID']      = $student->classesID;
                $array['sectionID']      = $student->sectionID;
                $array['studentgroupID'] = $student->studentgroupID;
                $array['onlineExamID']   = $onlineExamID;

                $userExamCheck = $this->online_exam_user_status_m->get_order_by_online_exam_user_status([
                    'userID'       => $userID,
                    'classesID'    => $array['classesID'],
                    'sectionID'    => $array['sectionID'],
                    'onlineExamID' => $onlineExamID
                ]);
            }

            $online_exam             = $this->online_exam_m->get_online_exam_by_student($array);
            $questionsBank                     = pluck($this->question_bank_m->get_order_by_question_bank(), 'obj', 'questionBankID');


            $onlineExamQuestions = $this->online_exam_question_m->get_order_by_online_exam_question(['onlineExamID' => $onlineExamID]);

            $allOnlineExamQuestions = $onlineExamQuestions;

            $pluckOnlineExamQuestions = pluck($allOnlineExamQuestions, 'questionID');
            $allAnswers = $this->question_answer_m->get_where_in_question_answer($pluckOnlineExamQuestions, 'questionID');




            foreach ($allAnswers as $answer) {
                if ($answer->typeNumber == 3) {
                    $mainQuestionAnswer[$answer->typeNumber][$answer->questionID][$answer->answerID] = $answer->text;
                } else {
                    $mainQuestionAnswer[$answer->typeNumber][$answer->questionID][] = $answer->optionID;
                }
            }

            $questionStatus    = [];
            $correctAnswer     = 0;
            $totalQuestionMark = 0;
            $totalCorrectMark  = 0;
            $visited           = [];

            $totalAnswer = 0;
            if (inicompute($userAnswer)) {
                foreach ($userAnswer as $userAnswerKey => $uA) {
                    $totalAnswer += inicompute($uA);
                }
            }

            if (inicompute($allOnlineExamQuestions)) {
                foreach ($allOnlineExamQuestions as $aoeq) {
                    if (isset($questionsBank[$aoeq->questionID])) {
                        $totalQuestionMark += $questionsBank[$aoeq->questionID]->mark;
                    }
                }
            }

            $f        = 0;
            $examtime = $this->online_exam_user_status_m->get_single_online_exam_user_status([
                'userID'       => $userID,
                'onlineExamID' => $onlineExamID
            ]);

            $examTimeCounter = 1;
            if (inicompute($examtime)) {
                $examTimeCounter = $examtime->examtimeID;
                $examTimeCounter++;
            }


            $statusID = 10;
            foreach ($mainQuestionAnswer as $typeID => $questions) {
                if (!isset($userAnswer[$typeID]))
                    continue;
                foreach ($questions as $questionID => $options) {
                    if (isset($onlineExamQuestions[$questionID])) {
                        $onlineExamQuestionID   = $onlineExamQuestions[$questionID]->onlineExamQuestionID;
                        $onlineExamUserAnswerID = $this->online_exam_user_answer_m->insert([
                            'onlineExamQuestionID' => $onlineExamQuestionID,
                            'userID'               => $userID,
                            'onlineExamID'         => $onlineExamID,
                            'examtimeID'           => $examTimeCounter
                        ]);
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
                        }

                        if ($f === 0) {
                            $questionStatus[$questionID] = 0;
                            $correctAnswer--;
                            $totalCorrectMark -= $questionsBank[$questionID]->mark;
                        }
                    }
                }
            }


            if (inicompute($online_exam)) {
                if ($online_exam->markType == 5) {

                    $percentage = 0;
                    if ($totalCorrectMark > 0 && $totalQuestionMark > 0) {
                        $percentage = (($totalCorrectMark / $totalQuestionMark) * 100);
                    }

                    $statusID = $percentage >=  $online_exam->percentage ? 5 : 10;
                } elseif ($online_exam->markType == 10) {
                    $statusID = $totalCorrectMark >=  $online_exam->percentage ? 5 : 10;
                }
            }

            $historyID = $this->online_exam_user_status_m->insert([
                'onlineExamID'       =>  $online_exam->onlineExamID,
                'time'               => $time,
                'totalQuestion'      => inicompute($onlineExamQuestions),
                'totalAnswer'        => $totalAnswer,
                'nagetiveMark'       =>  $online_exam->negativeMark,
                'duration'           =>  $online_exam->duration,
                'score'              => $correctAnswer,
                'userID'             => $userID,
                'classesID'          => inicompute($array['classesID']) ? $array['classesID'] : 0,
                'sectionID'          => inicompute($array['sectionID']) ? $array['sectionID'] : 0,
                'examtimeID'         => $examTimeCounter,
                'totalCurrectAnswer' => $correctAnswer,
                'totalMark'          => $totalQuestionMark,
                'totalObtainedMark'  => $totalCorrectMark,
                'totalPercentage'    => (($totalCorrectMark > 0 && $totalQuestionMark > 0) ? (($totalCorrectMark / $totalQuestionMark) * 100) : 0),
                'statusID'           => $statusID,
            ]);

            if ($online_exam->paid) {
                $onlineExamPayments = $this->online_exam_payment_m->get_single_online_exam_payment_only_first_row([
                    'online_examID' =>  $online_exam->onlineExamID,
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
            if (inicompute($student)) {
                $this->retdata['fail']        = $f;
                $this->retdata['historyID']   = $historyID;
                $this->retdata['studentName'] = $student->name;
                $this->retdata['studentImage'] = imagelink($student->photo);
                $this->retdata['examName'] = $online_exam->name;
                $this->retdata['examImage'] = imagelink($online_exam->photo);
                $this->retdata['obtainedMark']  = $totalCorrectMark;
                $this->retdata['totalMark'] = $totalQuestionMark;
                $this->retdata['correctAnswer']     = $correctAnswer;
                $this->retdata['wrongAnswer']     = $totalAnswer - $correctAnswer;
                $this->retdata['skipped']     = inicompute($onlineExamQuestions) - $totalAnswer;
            }



            $this->response([
                'status'    => true,
                'message'   => 'Result',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
        }
        $this->response([
            'status' => false,
            'message' => 'Invalid ID',
            'data' => []
        ], REST_Controller::HTTP_NOT_FOUND);
    }

    public function history_get()
    {
        $historyID     = htmlentities((string) escapeString($this->uri->segment(5)));
        $loginuserID   = $this->session->userdata('loginuserID');
        $onlineExams   = pluck($this->online_exam_m->get_online_exam(), 'name', 'onlineExamID');
        $allHistory    = [];
        $singleHistory = [];
        if ($historyID === '') {
            $examHistory = $this->online_exam_user_status_m->get_order_by_online_exam_user_status(['userID' => $loginuserID]);
            foreach ($examHistory as $key => $exam) {
                $allHistory[$key]['historyID'] = $exam->onlineExamUserStatus;
                $allHistory[$key]['onlineExamID'] = $exam->onlineExamID;
                $allHistory[$key]['name'] = $onlineExams[$exam->onlineExamID];
                $allHistory[$key]['totalMark'] = $exam->totalMark;
                $allHistory[$key]['totalObtainedMark'] = $exam->totalObtainedMark;
                $allHistory[$key]['image'] = imagelink($exam->image);
            }
        } else {
            $examHistory = $this->online_exam_user_status_m->get_single_online_exam_user_status(['onlineExamUserStatus' => $historyID]);
            if (inicompute($examHistory)) {
                $allOnlineExamQuestions = $this->online_exam_question_m->get_order_by_online_exam_question(['onlineExamID' => $examHistory->onlineExamID]);
                $availableQuestions = [];

                foreach ($allOnlineExamQuestions as $question) {
                    $availableQuestions[] = $question->questionID;
                }
                $questionsBanks                     = pluck($this->question_bank_m->get_order_by_question_bank(), 'obj', 'questionBankID');
                $examQuestions = [];
                $i = 0;
                foreach ($questionsBanks as  $question) {
                    if (in_array($question->questionBankID, $availableQuestions)) {
                        $examQuestions[$i]['question'] = strip_tags((string) $question->question);
                        $examQuestions[$i]['questionBankID'] = $question->questionBankID;
                        $examQuestions[$i]['explanation'] = $question->explanation;
                        $examQuestions[$i]['totalOption'] = $question->totalOption;
                        $examQuestions[$i]['typeNumber'] = $question->typeNumber;
                        $examQuestions[$i]['hints'] = $question->hints;
                        $examQuestions[$i]['image'] = empty($question->upload) ? null :  imagelink($question->upload);
                        $i++;
                    }
                }


                $this->retdata['questions'] = $examQuestions;
                $options    = [];
                $allOptions = [];
                $answers    = [];
                $allAnswers = [];
                if (inicompute($allOnlineExamQuestions) && inicompute($examQuestions)) {
                    $pluckOnlineExamQuestions = pluck($allOnlineExamQuestions, 'questionID');
                    $allOptions               = $this->question_option_m->get_where_in_question_option($pluckOnlineExamQuestions, 'questionID');

                    foreach ($examQuestions as $examQuestion) {
                        $j = 0;
                        foreach ($allOptions as $option) {

                            if ($examQuestion['questionBankID'] == $option->questionID) {
                                if ($option->name == "" && $option->img == "")
                                    continue;
                                $options[$option->questionID][$j]['optionID'] = $option->optionID;
                                $options[$option->questionID][$j]['questionID'] = $option->questionID;
                                $options[$option->questionID][$j]['name'] = $option->name;
                                $options[$option->questionID][$j]['image'] = empty($option->img) ? null : imagelink($option->img);
                                $j++;
                            }
                        }
                    }

                    $allAnswers = $this->question_answer_m->get_where_in_question_answer($pluckOnlineExamQuestions, 'questionID');
                    foreach ($allAnswers as $answer) {
                        $answers[$answer->questionID][] = $answer;
                    }
                    $this->retdata['answers'] = $answers;
                    $this->retdata['options'] = $options;
                } else {
                    $this->retdata['answers'] = $answers;
                    $this->retdata['options'] = $options;
                }

                $this->retdata['examquestionsuseranswer'] = pluck($this->online_exam_user_answer_option_m->get_order_by_online_exam_user_answer_option(array('userID'=>$loginuserID,'onlineExamID'=>$examHistory->onlineExamID,'examtimeID'=>$examHistory->examtimeID)), 'obj', 'questionID');
                $singleHistory['onlineExamID'] = $examHistory->onlineExamID;
                $singleHistory['name'] = $onlineExams[$examHistory->onlineExamID];
                $singleHistory['totalQuestion'] = $examHistory->totalQuestion;
                $singleHistory['totalPercentage'] = $examHistory->totalPercentage;
                $singleHistory['totalAnswer'] = $examHistory->totalAnswer;
                $singleHistory['right'] = $examHistory->totalCurrectAnswer;
                $singleHistory['wrong'] = $examHistory->totalAnswer - $examHistory->totalCurrectAnswer;
                $singleHistory['skipped'] = $examHistory->totalQuestion - $examHistory->totalAnswer;
                $singleHistory['totalMark'] = $examHistory->totalMark;
                $singleHistory['totalObtainedMark'] = $examHistory->totalObtainedMark;
                $singleHistory['image'] = imagelink($examHistory->image);
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Invalid ID',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }

        $this->retdata['examHistory'] = inicompute($allHistory) ? $allHistory : $singleHistory;
        $this->response([
            'status'    => true,
            'message'   => 'Exam History',
            'data'      => $this->retdata
        ], REST_Controller::HTTP_OK);
    }

    public function instruction_get() //done
    {
        $onlineExamID = htmlentities((string) escapeString($this->uri->segment(5)));
        if ((int)$onlineExamID !== 0) {
            $instructions             = pluck($this->instruction_m->get_order_by_instruction(), 'obj', 'instructionID');
            $onlineExam               = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
            $this->retdata['onlineExamID'] = $onlineExamID;
            if (!isset($instructions[$onlineExam->instructionID])) {
                $this->response([
                    'status' => false,
                    'message' => 'No Instruction Found',
                    'data' => $this->retdata
                ], REST_Controller::HTTP_NOT_FOUND);
            }
            $this->retdata['instruction'] = $instructions[$onlineExam->instructionID];
            $this->response([
                'status'    => true,
                'message'   => 'Success',
                'data'      => $this->retdata
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Error 404',
                'data' => $this->retdata
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function paymentInfo_get() //done
    {
        $onlineExamID = htmlentities((string) escapeString($this->uri->segment(5)));
        $this->retdata['payableamount'] = 0.00;
        if (permissionChecker('take_exam')) {
            if ($onlineExamID !== '' && (int)$onlineExamID && $onlineExamID > 0) {
                $onlineExam = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
                if (inicompute($onlineExam)) {
                    $this->retdata['payableamount'] = sprintf("%.2f", $onlineExam->cost);
                    $this->response([
                        'status'    => true,
                        'message'   => 'Success',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'status' => 404,
                        'message' => 'Exam Not Found',
                        'data' => []
                    ], REST_Controller::HTTP_NOT_FOUND);
                }
            }
            $this->response([
                'status' => 404,
                'message' => 'Exam ID Missing',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        } else {
            $this->response([
                'status' => 403,
                'message' => 'Permission Denied',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function paymentList_get() //done
    {
        $onlineExamID = htmlentities((string) escapeString($this->uri->segment(5)));
        $this->retdata['onlineExamID'] = $onlineExamID;
        if ($onlineExamID !== '' && (int)$onlineExamID && $onlineExamID > 0) {
            $onlineExam = $this->online_exam_m->get_single_online_exam(['onlineExamID' => $onlineExamID]);
            if (inicompute($onlineExam)) {
                $this->retdata['onlineExamPayments'] = $this->online_exam_payment_m->get_order_by_online_exam_payment([
                    'online_examID' => $onlineExamID,
                    'usertypeID'    => $this->session->userdata('usertypeID'),
                    'userID'        => $this->session->userdata('loginuserID')
                ]);

                if (inicompute($this->retdata['onlineExamPayments'])) {
                    $this->response([
                        'status'    => true,
                        'message'   => 'Success',
                        'data'      => $this->retdata
                    ], REST_Controller::HTTP_OK);
                }

                $this->response([
                    'status' => false,
                    'message' => 'No Payment Done Yet',
                    'data' => []
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        $this->response([
            'status' => false,
            'message' => 'Error 404',
            'data' => []
        ], REST_Controller::HTTP_NOT_FOUND);
    }

    public function makePayment_post()
    {

        if (inputCall('onlineExamID')) {
            $invoice_data = $this->online_exam_m->get_single_online_exam(['onlineExamID' => inputCall('onlineExamID')]);
            if (($invoice_data->paid == 1) && ((float)$invoice_data->cost == 0)) {
                $this->response([
                    'status'    => false,
                    'message'   => 'Exam Amount Can\'t Be Zero',
                    'data'      => []
                ], REST_Controller::HTTP_OK);
            }

            if (($invoice_data->examStatus == 1) && ($invoice_data->paid == 1) && isset($this->data['paindingpayments'][$invoice_data->onlineExamID])) {
                $this->response([
                    'status'    => false,
                    'message'   => 'This exam price already paid',
                    'data'      => []
                ], REST_Controller::HTTP_OK);
            }
            $paymentService = new PaymentService(inputCall('transaction_id'));
            $paymentService->add_transaction([
                'online_exam_id' => inputCall('online_exam_id'),
                'amount'         => inputCall('amount'),
                'payment_method' => inputCall('paymentMethod')
            ]);
            if ($this->data['ApiPaymentStatus']) {
                $this->response([
                    'status'    => true,
                    'message'   => 'Payment Completed',
                    'data'      => []
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'status'    => true,
                    'message'   => 'Payment Completed',
                    'data'      => []
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'status' => false,
                'message' => 'Exam Not Found',
                'data' => []
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
