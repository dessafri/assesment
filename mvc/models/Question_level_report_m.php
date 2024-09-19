<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Question_level_report_m extends MY_Model
{

    protected $_table_name = 'question_level_report';
    protected $_primary_key = 'questionLevelReportID';
    protected $_primary_filter = 'intval';
    protected $_order_by = "questionLevelReportID asc";

    public function __construct()
    {
        parent::__construct();
    }

    public function insert_question_level_report($array)
    {
        $error = parent::insert($array);
        return true;
    }
    public function insert_batch_question_level_report($array)
    {
        if (!empty($array)) {
            $this->db->insert_batch('question_level_report', $array); // Replace 'your_table_name' with the actual table name
        }
        return true;
    }

    public function update_question_level($data, $id = null)
    {
        parent::update($data, $id);
        return $id;
    }

    public function delete_question_level($id)
    {
        parent::delete($id);
    }
    public function compute_jawaban($iduser,$idrelasi,$idexam)
    {
        $query = "SELECT c.levelID AS questionLevelID,c.groupID,a.relasi_jabatan AS userID,CASE
        WHEN d.title = 'Atasan' THEN SUM(b.nilaijawaban) * 0.2
        WHEN d.title = 'Bawahan' THEN SUM(b.nilaijawaban) * 0.5
        WHEN d.title = 'Rekanan' THEN SUM(b.nilaijawaban) * 0.3
        ELSE SUM(b.nilaijawaban)
        END AS value,
        a.onlineExamID AS examID
        FROM online_exam_user_answer_option a 
        JOIN question_option b ON a.optionID = b.optionID
        JOIN question_bank c ON c.questionBankID = b.questionID
        JOIN question_group d ON d.questionGroupID = c.groupID 
        WHERE 
        a.userID = '$iduser'
        AND a.relasi_jabatan = '$idrelasi'
        AND a.onlineExamID = '$idexam'
        GROUP BY c.levelID
        ";
                $result = $this->db->query($query);
                return $result->result_array();
            }
    public function report_subtype(){
            $query = "SELECT
            userID,
            groupID,
            questionLevelID,
            AVG(value) AS value,
            question_group.title as name
        FROM
            question_level_report
         JOIN question_group ON question_level_report.groupID = question_group.questionGroupID
        GROUP BY
            userID,
            groupID,
            questionLevelID
        ";   
        $result = $this->db->query($query);
        return $result->result_array();     
    }
    public function end_report(){
            $query = "SELECT
                    userID,
                    AVG(value) AS nilai_akhir
                FROM
                    question_level_report
                GROUP BY
                    userID";   
                $result = $this->db->query($query);
                return $result->result_array();     
            }
    public function total_report(){
            $query = "SELECT
                    userID,
                    SUM(value) AS total_akhir
                FROM
                    question_level_report
                GROUP BY
                    userID";   
                $result = $this->db->query($query);
                return $result->result_array();     
            }
    public function report_type(){
            $query = "WITH LatestExamPerUser AS (
            SELECT
                userID,
                MAX(examID) AS latestExamID
            FROM
                question_level_report
            GROUP BY
                userID
        )
        SELECT
            q.userID,
            q.questionLevelID,
            q.value,
            q.examID
        FROM
            question_level_report q
        JOIN
            LatestExamPerUser l ON q.userID = l.userID AND q.examID = l.latestExamID
        ORDER BY
            q.userID,
            q.examID DESC,
            q.questionLevelID;
        ";   
                $result = $this->db->query($query);
                return $result->result_array();     
            }
    public function report_type_limit2(){
            $query = "WITH RankedExams AS (
            SELECT
                examID,
                ROW_NUMBER() OVER (ORDER BY examID DESC) AS rn
            FROM
                question_level_report
            GROUP BY
                examID
        ),
        LastTwoExams AS (
            SELECT
                examID
            FROM
                RankedExams
            WHERE
                rn <= 2
        )
        SELECT
            a.examID,
            a.userID,
            a.questionLevelID,
            a.value AS value
        FROM
            question_level_report a
        JOIN
            LastTwoExams b ON a.examID = b.examID
        GROUP BY
            a.examID,
            a.userID,
            a.questionLevelID
        ORDER BY
            a.examID DESC,
            a.userID,
            a.questionLevelID
        ";   
                $result = $this->db->query($query);
                return $result->result_array();     
    }
}

