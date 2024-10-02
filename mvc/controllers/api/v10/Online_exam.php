<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Online_exam extends Api_Controller
{
  public $student_m;
  public $retdata;
  public $classes_m;
  public $online_exam_user_status_m;
  public $exam_type_m;
  function __construct()
  {
    parent::__construct();
    $this->load->model("online_exam_m");
    $this->load->model("subject_m");
    $this->load->model("classes_m");
    $this->load->model("exam_type_m");
    $this->load->model("exam_type_m");
    $this->load->model("student_m");
    $this->load->model("online_exam_user_status_m");
  }

  public function index_get()
  {
    $usertypeID  = $this->session->userdata('usertypeID');
    $loginuserID = $this->session->userdata('loginuserID');
    $userSubjectPluck = [];
    $onlineExams = [];

    if ($usertypeID == '3') {
      $student = $this->student_m->get_single_student(array('studentID' => $loginuserID));
      if (inicompute($student)) {
        $userSubjectPluck = pluck($this->subject_m->get_order_by_subject(array('classesID' => $student->classesID, 'type' => 1)), 'subjectID', 'subjectID');
        $optionalSubject = $this->subject_m->get_single_subject(array('type' => 0, 'subjectID' => $student->optionalsubjectID));
        if (inicompute($optionalSubject)) {
          $userSubjectPluck[$optionalSubject->subjectID] = $optionalSubject->subjectID;
        }
      }
      $online_exams = $this->online_exam_m->get_order_by_online_exam();
      if (customCompute($online_exams)) {
        foreach ($online_exams as $online_exam) {
          if ((($student->classesID == $online_exam->classID) || ($online_exam->classID == '0')) && (($student->sectionID == $online_exam->sectionID) || ($online_exam->sectionID == '0')) && (($student->studentgroupID == $online_exam->studentGroupID) || ($online_exam->studentGroupID == '0')) && ($online_exam->published == '1') && (($online_exam->subjectID == '0') || (in_array($online_exam->subjectID, $userSubjectPluck)))) {
            $onlineExams[] = $online_exam;
          }
        }
        $this->retdata['onlineExams'] = $this->common($onlineExams);
      } else {
        $this->response([
          'status' => false,
          'message' => 'No Exam Found',
          'data' => []
        ], REST_Controller::HTTP_NOT_FOUND);
      }
    }

    $this->retdata['usertypeID']   = $usertypeID;
    $this->retdata['onlineExams']   =  $this->retdata['onlineExams'];
    $this->response([
      'status' => true,
      'message' => 'Success',
      'data' => $this->retdata
    ], REST_Controller::HTTP_OK);
  }

  public function subjectWise_get()
  {
    $subjectID = htmlentities((string) escapeString($this->uri->segment(5)));
    if ($subjectID !== '' && (int)$subjectID && $subjectID > 0) {
      $onlineExams = $this->online_exam_m->get_order_by_online_exam(['subjectID' => $subjectID], ['published' => '1']);
      if (inicompute($onlineExams)) {

        $this->retdata['onlineExams'] = $this->common($onlineExams);
        $this->response([
          'status' => true,
          'message' => 'Success',
          'data' => $this->retdata
        ], REST_Controller::HTTP_OK);
      }
      $this->response([
        'status' => true,
        'message' => 'This Subject Doesn\'t Have Any Exam.',
        'data' => []
      ], REST_Controller::HTTP_OK);
    }
    $this->response([
      'status' => false,
      'message' => 'Invalid Subject ID',
      'data' => []
    ], REST_Controller::HTTP_NOT_FOUND);
  }

  public function common($onlineExams)
  {
    $onlineExamData = [];
    $examGivenStatus = FALSE;

    $userID = $this->session->userdata('loginuserID');
    $this->data['student'] = $this->student_m->get_student($userID);
    if (inicompute($this->data['student'])) {
      $array['classesID']      = $this->data['student']->classesID;
      $array['sectionID']      = $this->data['student']->sectionID;
      $array['studentgroupID'] = $this->data['student']->studentgroupID;
    }
    $classes = pluck($this->classes_m->get_classes(), 'classes', 'classesID');

    $i = 0;
    foreach ($onlineExams as $key => $onlineExam) {

      $userExamCheck = $this->online_exam_user_status_m->get_order_by_online_exam_user_status([
        'userID'       => $userID,
        'classesID'    => $array['classesID'],
        'sectionID'    => $array['sectionID'],
        'onlineExamID' => $onlineExam->onlineExamID
      ]);

      if ($onlineExam->examStatus == 2) {
        $examGivenStatus = FALSE;
      } else {
        $userExamCheck = pluck($userExamCheck, 'obj', 'onlineExamID');
        $examGivenStatus = isset($userExamCheck[$onlineExam->onlineExamID]);
      }

      if ($examGivenStatus == false) {

        if ($onlineExam->examTypeNumber == '4') {
          $presentDate   = strtotime(date('Y-m-d'));
          $examStartDate = strtotime((string) $onlineExam->startDateTime);
          $examEndDate   = strtotime((string) $onlineExam->endDateTime);
          $StartDate = date('d M Y', strtotime((string) $onlineExam->startDateTime));
          $EndDate   = date('d M Y', strtotime((string) $onlineExam->endDateTime));
        } elseif ($onlineExam->examTypeNumber == '5') {
          $presentDate   = strtotime(date('Y-m-d H:i:s'));
          $examStartDate = strtotime((string) $onlineExam->startDateTime);
          $examEndDate   = strtotime((string) $onlineExam->endDateTime);
          $StartDate = date('h:i a d M Y', strtotime((string) $onlineExam->startDateTime));
          $EndDate   = date('h:i a d M Y', strtotime((string) $onlineExam->endDateTime));
          $date =  $StartDate . ' - ' . $EndDate;
        } else {
          $StartDate = '';
          $EndDate = '';
        }

        $examExpireStatus = FALSE;
        $examUpcomingStatus = FALSE;
        if ($onlineExam->examTypeNumber == '4' || $onlineExam->examTypeNumber == '5') {
          if ($presentDate >= $examStartDate && $presentDate <= $examEndDate) {
            $examExpireStatus = FALSE;
          } elseif ($presentDate > $examStartDate && $presentDate > $examEndDate) {
            $examExpireStatus = TRUE;
          } else {
            $examUpcomingStatus = TRUE;
          }
        } else {
          $examExpireStatus = FALSE;
        }
        $counter = '00:00';
        if ($examUpcomingStatus) {
          $counter = count_end_date($StartDate, $EndDate,$onlineExam->examTypeNumber);
        }
        $examTypeNumber       = pluck($this->exam_type_m->get_exam_type(), 'title', 'examTypeNumber');
        $time                 = $onlineExam->examTypeNumber == 5 ? date('h:i a', strtotime($StartDate)) : '00:00';
        $onlineExamData[$i]['onlineExamID']   = $onlineExam->onlineExamID;
        $onlineExamData[$i]['name']           = $onlineExam->name;
        $onlineExamData[$i]['paid']           = $onlineExam->paid ? 'Paid' : 'Free';
        $onlineExamData[$i]['cost']           = $onlineExam->cost;
        $onlineExamData[$i]['expired']        = $examExpireStatus;
        $onlineExamData[$i]['upcoming']       = $examUpcomingStatus;
        $onlineExamData[$i]['duration']       = $onlineExam->duration;
        $onlineExamData[$i]['negativeMark']   = $onlineExam->negativeMark;
        $onlineExamData[$i]['bonusMark']      = $onlineExam->bonusMark;
        $onlineExamData[$i]['examTypeNumber'] = $onlineExam->examTypeNumber;
        $onlineExamData[$i]['examTypeName']   = $examTypeNumber[$onlineExam->examTypeNumber];
        $onlineExamData[$i]['instructionID']  = $onlineExam->instructionID;
        $onlineExamData[$i]['classID']        = $onlineExam->classID;
        $onlineExamData[$i]['image']          = imagelink($onlineExam->image);
        $onlineExamData[$i]['className']      = $classes[$onlineExam->classID];
        $onlineExamData[$i]['startDateTime']  = $StartDate;
        $onlineExamData[$i]['endDateTime']    = $EndDate;
        $onlineExamData[$i]['time']           = $time;
        $onlineExamData[$i]['counter']        = $counter;
        $onlineExamData[$i]['date']           = $date;
        $onlineExamData[$i]['showMarkAfterExam']           = $onlineExam->showMarkAfterExam ? $onlineExam->showMarkAfterExam : 0 ;
        $onlineExamData[$i]['showResultAfterExam']           = $onlineExam->showResultAfterExam ? $onlineExam->showResultAfterExam : 0 ;
        $i++;
      }
    }
    return $onlineExamData;
  }
}
