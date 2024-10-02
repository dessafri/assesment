<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') || exit('No direct script access allowed');

class Question_bank extends Api_Controller
{
  public $load;
  public $session;
  public $lang;
  public $retdata;
  function __construct()
  {
    parent::__construct();
    $this->load->model("question_bank_m");
    $this->load->model("question_group_m");
    $this->load->model("question_level_m");
    $this->load->model("question_type_m");
    $language = $this->session->userdata('lang');
    $this->lang->load('question_bank', $language);
  }

  public function index_get()
  {
    $this->retdata['groups']         = pluck($this->question_group_m->get_order_by_question_group(), 'obj', 'questionGroupID');
    $this->retdata['levels']         = pluck($this->question_level_m->get_order_by_question_level(), 'obj', 'questionLevelID');
    $this->retdata['types']          = pluck($this->question_type_m->get_order_by_question_type(), 'obj', 'typeNumber');
    $this->retdata['question_banks'] = $this->question_bank_m->get_order_by_question_bank();

    $this->response([
      'status' => true,
      'message' => 'Success',
      'data' => $this->retdata
    ], REST_Controller::HTTP_OK);
  }
}
