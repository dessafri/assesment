<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Question_bank_m extends MY_Model {

    protected $_table_name = 'question_bank';
    protected $_primary_key = 'questionBankID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "questionBankID desc";

    public function __construct() 
    {
        parent::__construct();
    }

    public function get_question_bank($array=NULL, $signal=FALSE) 
    {
        $query = parent::get($array, $signal);
        return $query;
    }

    public function get_single_question_bank($array) 
    {
        $query = parent::get_single($array);
        return $query;
    }

    public function get_order_by_question_bank($array=NULL) 
    {
        $query = parent::get_order_by($array);
        return $query;
    }

    public function insert_question_bank($array) 
    {
        $id = parent::insert($array);
        return $id;
    }

    public function update_question_bank($data, $id = NULL) 
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_question_bank($id)
    {
        parent::delete($id);
    }

    public function get_question_bank_wherein($array=NULL, $key=FALSE) {
        $query = parent::get_where_in($array, $key);
        return $query;
    }

    public function get_question_bank_questionArray($array=NULL, $key=FALSE) {
        $query = parent::get_where_in($array, $key);
        return $query;
    }

    public function get_question_bank_group_by($array=NULL, $key=FALSE, $group=FALSE) {
        $this->db->where_in($key, $array);
        $this->db->group_by($group);
        $query = $this->db->get($this->_table_name);
        $query = $query->result();

        return $query;
    }
}
