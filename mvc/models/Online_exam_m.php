<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Online_exam_m extends MY_Model {

    protected $_table_name = 'online_exam';
    protected $_primary_key = 'onlineExamID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "onlineExamID desc";

    public function __construct() 
    {
        parent::__construct();
    }

    public function get_online_exam($array=NULL, $signal=FALSE) 
    {
        $query = parent::get($array, $signal);
        return $query;
    }

    public function get_single_online_exam($array) 
    {
        $this->db->order_by('onlineExamID', 'DESC');
        $this->db->limit(1);
        $query = parent::get_single($array);
        return $query;
    }

    public function get_order_by_online_exam($array=NULL) 
    {
        $query = parent::get_order_by($array);
        return $query;
    }

    public function insert_online_exam($array) 
    {
        $id = parent::insert($array);
        return $id;
    }

    public function update_online_exam($data, $id = NULL) 
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_online_exam($id)
    {
        parent::delete($id);
    }

    public function get_online_exam_by_student($array) 
    {   
        $query = "SELECT * FROM online_exam WHERE (classID='".$array['classesID']."' || classID='0') && (sectionID='".$array['sectionID']."' || sectionID='0') && (studentgroupID='".$array['studentgroupID']."' || studentgroupID='0') && published='1' && onlineExamID='".$array['onlineExamID']."'";
        $result = $this->db->query($query);
        return $result->row();
    }

    public function get_online_exam_group($data){
        // Sanitize and prepare the data
        $data = '%' . strtolower($data) . '%';
        
        // Query with binding parameter
        $query = "SELECT questionGroupID FROM question_group WHERE LOWER(question_group.title) LIKE ?";
        $result = $this->db->query($query, array($data));
        
        // Return the result
        return $result->row();
    }
     
    public function get_online_exam_by_group($data){
        $query = "SELECT * FROM online_exam WHERE groupID = '$data'";
        $result = $this->db->query($query);
        return $result->row();
    } 

}
