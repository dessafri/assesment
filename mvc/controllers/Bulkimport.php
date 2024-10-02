<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Bulkimport extends Admin_Controller
{
    public $load;
    public $session;
    public $lang;
    public $data;
    public $upload;
    public $csvimport;
    public $question_bank_m;
    public $question_option_m;
    public $question_answer_m;
    public $question_group_m;
    public $question_level_m;
    public $question_type_m;
    /*
    | -----------------------------------------------------
    | PRODUCT NAME:     INILABS SCHOOL MANAGEMENT SYSTEM
    | -----------------------------------------------------
    | AUTHOR:            INILABS TEAM
    | -----------------------------------------------------
    | EMAIL:            info@inilabs.net
    | -----------------------------------------------------
    | COPYRIGHT:        RESERVED BY INILABS IT
    | -----------------------------------------------------
    | WEBSITE:            http://inilabs.net
    | -----------------------------------------------------
     */
    public function __construct()
    {
        parent::__construct();

        $this->load->model("question_bank_m");
        $this->load->model("question_group_m");
        $this->load->model("question_level_m");
        $this->load->model("question_type_m");
        $this->load->model("question_answer_m");
        $this->load->model("question_option_m");
        $this->load->model("online_exam_question_m");
        $this->load->library('csvimport');

        $language = $this->session->userdata('lang');
        $this->lang->load('bulkimport', $language);
    }

    public function index()
    {
        $this->data["subview"] = "bulkimport/index";
        $this->load->view('_layout_main', $this->data);
    }

    // public function question_bulkimport() {
    //     if(isset($_FILES["csvQuestion"])) {
    //         $upload_path = "./uploads/csv/";
    //         // Check if the directory exists, if not create it
    //         if (!is_dir($upload_path)) {
    //             mkdir($upload_path, 0755, TRUE);
    //         }
    //         $config['upload_path']   = $upload_path;
    //         $config['allowed_types'] = 'text/plain|text/csv|csv';
    //         $config['max_size']      = '2048';
    //         $config['file_name']     = $_FILES["csvQuestion"]['name'];
    //         $config['overwrite']     = TRUE;
    //         $this->load->library('upload', $config);
    //         if(!$this->upload->do_upload("csvQuestion")) {
    //             $this->upload->display_errors();
    //             $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
    //             redirect(base_url("bulkimport/index"));
    //         } else {
    //             $file_data      = $this->upload->data();
    //             $file_path      =  './uploads/csv/'.$file_data['file_name'];
    //             $column_headers = array("Question Group", "Difficulty Level", "Question", "Explanation","Hints", "Mark", "Question Type", "Total Option", "Options","Correct Answer");

    //             if($csv_array = @$this->csvimport->get_array($file_path, $column_headers)) {
    //                 if(customCompute($csv_array)) {
    //                     $i       = 1;
    //                     $msg     = "";
    //                     $csv_col = [];
    //                     foreach ($csv_array as $row) {
    //                         if ($i==1) {
    //                             $csv_col = array_keys($row);
    //                         }
    //                         $match       = array_diff($column_headers, $csv_col);
    //                         if (customCompute($match) <= 0) {
    //                             $array = $this->arrayToPost($row);
    //                             $singleQuestionCheck = $this->singleQuestionCheck($array);
    //                             if($singleQuestionCheck['status']) {
    //                                 $levelID = $this->get_id($row['Difficulty Level'],'level');
    //                                 $groupID = $this->get_id($row['Question Group'],'group');
    //                                 $typeID = $this->get_id($row['Question Type'],'type');
    //                                 $insert_data = array(
    //                                     'question'          => $row['Question'],
    //                                     'explanation'       => $row['Explanation'],
    //                                     'levelID'           => $levelID,
    //                                     'groupID'           => $groupID,
    //                                     'totalOption'       => $row['Total Option'],
    //                                     'typeNumber'        => $typeID,
    //                                     'mark'              => $row['Mark'],
    //                                     'hints'             => $row['Hints'],
    //                                     "create_date"       => date("Y-m-d h:i:s"),
    //                                     "modify_date"       => date("Y-m-d h:i:s"),
    //                                     "create_userID"     => $this->session->userdata('loginuserID'),
    //                                     "create_usertypeID" => $this->session->userdata('usertypeID'),
    //                                 );
    //                                 $questionID = $this->question_bank_m->insert_question_bank($insert_data);
    //                                 $questionType = $this->get_id($row['Question Type'],'type');
    //                                 $totalOption = $row['Total Option'];
    //                                 $options = explode(',',(string) $row['Options']);
    //                                 $totalAnswers = explode(',',(string) $row['Correct Answer']);
    //                                 $answers = [];
    //                                 foreach($options as $key => $option){
    //                                     foreach ($totalAnswers as $singleAnswer){
    //                                         if($singleAnswer === $option) {
    //                                             $answers[$key] = $singleAnswer;
    //                                         }
    //                                     }
    //                                 }
    //                                 if($questionType == 1 || $questionType == 2) {

    //                                     $getQuestionOptions = pluck($this->question_option_m->get_order_by_question_option(['questionID' => $questionID]), 'optionID');

    //                                     if(!inicompute($getQuestionOptions)) {
    //                                         foreach (range(1,10) as $optionID) {
    //                                             $data = [
    //                                                 'name' => '',
    //                                                 'questionID' => $questionID
    //                                             ];
    //                                             $getQuestionOptions[] = $this->question_option_m->insert_question_option($data);
    //                                         }
    //                                     }

    //                                     foreach ($options as $key => $option) {
    //                                         if($option == '') {
    //                                             $totalOption--;
    //                                             continue;
    //                                         }

    //                                         $data = [
    //                                             'name' => $option,
    //                                         ];

    //                                         $this->question_option_m->update_question_option($data, $getQuestionOptions[$key]);

    //                                         if(array_key_exists($key, $answers)) {
    //                                             $ansData = [
    //                                                 'questionID' => $questionID,
    //                                                 'optionID' => $getQuestionOptions[$key],
    //                                                 'typeNumber' =>$questionType
    //                                             ];
    //                                             $this->question_answer_m->insert_question_answer($ansData);
    //                                         }
    //                                     }

    //                                     if($totalOption != $row['Total Option']) {
    //                                         $this->question_bank_m->update_question_bank(['totalOption' => $totalOption], $questionID);
    //                                     }
    //                                 }
    //                                 elseif ($questionType == 3) {
    //                                     foreach ($answers as $answer) {
    //                                         if($answer === '') {
    //                                             $totalOption--;
    //                                             continue;
    //                                         }
    //                                         $ansData = [
    //                                             'questionID' => $questionID,
    //                                             'text' => $answer,
    //                                             'typeNumber' =>$questionType
    //                                         ];
    //                                         $this->question_answer_m->insert_question_answer($ansData);

    //                                     }
    //                                     if($totalOption != $row['Total Option']) {
    //                                         $this->question_bank_m->update_question_bank(['totalOption' => $totalOption], $questionID);
    //                                     }
    //                                 }

    //                             } else {
    //                                 $msg .= $i.". ". $row['Question']." is not added! , ";
    //                                 $msg .= implode(' , ', $singleQuestionCheck['error']);
    //                                 $msg .= ". <br/>";
    //                             }
    //                         } else {
    //                             $this->session->set_flashdata('error', "Wrong csv file!");
    //                             redirect(base_url("bulkimport/index"));
    //                         }
    //                         $i++;
    //                     }
    //                     if($msg != "") {
    //                         $this->session->set_flashdata('msg', $msg);
    //                         $this->session->set_flashdata('error', $this->lang->line('bulkimport_error'));
    //                         redirect(base_url("bulkimport/index"));
    //                     }
    //                     $this->session->set_flashdata('success', $this->lang->line('bulkimport_success'));
    //                     redirect(base_url("bulkimport/index"));
    //                 } else {
    //                     $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
    //                     redirect(base_url("bulkimport/index"));
    //                 }
    //             } else {
    //                 $this->session->set_flashdata('error', "Wrong csv file!");
    //                 redirect(base_url("bulkimport/index"));
    //             }
    //         }
    //     } else {
    //         $this->session->set_flashdata('error', $this->lang->line('bulkimport_select_file'));
    //         redirect(base_url("bulkimport/index"));
    //     }
    // }
    public function question_bulkimport()
    {
        if (isset($_FILES["csvQuestion"])) {
            $upload_path = "./uploads/csv/";

            // Check if the directory exists, if not create it
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'text/plain|text/csv|csv';
            $config['max_size'] = '2048';
            $config['file_name'] = $_FILES["csvQuestion"]['name'];
            $config['overwrite'] = true;

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload("csvQuestion")) {
                $this->upload->display_errors();
                $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
                redirect(base_url("bulkimport/index"));
            } else {
                $file_data = $this->upload->data();
                $file_path = './uploads/csv/' . $file_data['file_name'];
                $column_headers = array("name", "dob", "sex", "religion", "email", "phone", "address", "departementID", "registerNO", "perusahaan_id", "username");
                $csv_array = @$this->csvimport->get_array($file_path, $column_headers);
                if ($csv_array) {
                    if (customCompute($csv_array)) {
                        $i = 1;
                        $msg = "";
                        $csv_col = [];

                        foreach ($csv_array as $row) {

                            // var_dump($row['classID']);
                            // exit;
                            if ($i == 1) {
                                $csv_col = array_keys($row);
                            }

                            $match = array_diff($column_headers, $csv_col);
                            if (customCompute($match) <= 0) {
                                $insert_data = array(
                                    'name' => $row['name'],
                                    'dob' => date('Y-m-d', strtotime($row['dob'])),
                                    'sex' => $row['sex'],
                                    'religion' => $row['religion'],
                                    'email' => $row['email'],
                                    'phone' => $row['phone'],
                                    'address' => $row['address'],
                                    'classesID' => $row['departementID'],
                                    'roll' => "1111",
                                    'registerNO' => $row['registerNO'],
                                    'parentID' => $row['perusahaan_id'],
                                    'username' => $row['username'],
                                    'password' => rand(0, 99999999),
                                    // 'Atasan' => $row['Atasan'],
                                    // 'Rekanan' => $row['Rekanan'],
                                    // 'Bawahan' => $row['Bawahan'],
                                    "create_date" => date("Y-m-d h:i:s"),
                                    "modify_date" => date("Y-m-d h:i:s"),
                                    "create_userID" => $this->session->userdata('loginuserID'),
                                    "create_username" => $this->session->userdata('username'),
                                    "create_usertype" => $this->session->userdata('usertype'),
                                );

                                $this->db->insert('student', $insert_data);
                                $studentID = $this->db->insert_id();
                                // Insert into studentextend table
                                $studentextend_data = array(
                                    'studentID' => $studentID, // Add other fields as needed
                                );
                                $this->db->insert('studentextend', $studentextend_data);
                            } else {
                                $this->session->set_flashdata('error', "Wrong csv file!");
                                redirect(base_url("bulkimport/index"));
                            }
                            $i++;
                        }

                        if ($msg != "") {
                            $this->session->set_flashdata('msg', $msg);
                            $this->session->set_flashdata('error', $this->lang->line('bulkimport_error'));
                            redirect(base_url("bulkimport/index"));
                        }

                        $this->session->set_flashdata('success', $this->lang->line('bulkimport_success'));
                        redirect(base_url("bulkimport/index"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
                        redirect(base_url("bulkimport/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', "Wrong csv file!");
                    redirect(base_url("bulkimport/index"));
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('bulkimport_select_file'));
            redirect(base_url("bulkimport/index"));
        }
    }
    public function relation_bulkimport()
    {
        if (isset($_FILES["csvRelation"])) {
            $upload_path = "./uploads/csv/";

            // Check if the directory exists, if not create it
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0755, true);
            }

            $config['upload_path'] = $upload_path;
            $config['allowed_types'] = 'text/plain|text/csv|csv';
            $config['max_size'] = '2048';
            $config['file_name'] = $_FILES["csvRelation"]['name'];
            $config['overwrite'] = true;

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload("csvRelation")) {
                $this->upload->display_errors();
                $this->session->set_flashdata('error', $this->lang->line('bulkimport_upload_fail'));
                redirect(base_url("bulkimport/index"));
            } else {
                $file_data = $this->upload->data();
                $file_path = './uploads/csv/' . $file_data['file_name'];
                $column_headers = array("No", "Nama", "Level", "Menilai", "Menilai_Level", "Status");
                $csv_array = @$this->csvimport->get_array($file_path, $column_headers);

                if ($csv_array) {
                    if (customCompute($csv_array)) {
                        $i = 1;
                        $msg = "";
                        $csv_col = [];

                        foreach ($csv_array as $row) {
                            if ($i == 1) {
                                $csv_col = array_keys($row);
                            }

                            $match = array_diff($column_headers, $csv_col);
                            if (customCompute($match) <= 0) {
                                // Check if "Nama" exists in the database and get its ID
                                $nama = $row['Nama'];
                                $this->db->like('name', $nama);
                                $nama_query = $this->db->get('student');
                                if ($nama_query->num_rows() > 0) {
                                    $nama_row = $nama_query->row();
                                    $nama_id = $nama_row->studentID;
                                } else {
                                    // If "Nama" does not exist, throw an error
                                    $this->session->set_flashdata('error', "Nama '$nama' not found in the database!");
                                    redirect(base_url("bulkimport/index"));
                                    exit;
                                }

                                // Prepare relational data
                                $evaluations = [
                                    ['menilai' => $row['Menilai'], 'menilai_level' => $row['Menilai_Level'], 'status' => $row['Status']],
                                ];

                                foreach ($evaluations as $evaluation) {
                                    if (!empty($evaluation['menilai'])) {
                                        // Check if "Menilai" exists in the database and get its ID
                                        $menilai = $evaluation['menilai'];
                                        $this->db->like('name', $menilai);
                                        $menilai_query = $this->db->get('student');
                                        if ($menilai_query->num_rows() > 0) {
                                            $menilai_row = $menilai_query->row();
                                            $menilai_id = $menilai_row->studentID;
                                        } else {
                                            // If "Menilai" does not exist, insert it and get its ID
                                            $this->session->set_flashdata('error', "Nama '$menilai' not found in the database!");
                                            redirect(base_url("bulkimport/index"));
                                            exit;
                                        }

                                        // Insert relational data
                                        $relasi_data = array(
                                            'user' => $nama_id,
                                            'user_relation' => $menilai_id,
                                            'keterangan' => 
    ($evaluation['status'] == 'Rekanan') ? 'Rekanan' : 
    (($evaluation['status'] == 'Bawahan') ? 'Bawahan' : 
    (($evaluation['status'] == 'Diri Sendiri') ? 'Diri Sendiri' : 'Atasan')),

                                        );
                                        $this->db->insert('relasi_jabatan', $relasi_data);
                                    }
                                }
                            } else {
                                $this->session->set_flashdata('error', "Wrong csv file!");
                                redirect(base_url("bulkimport/index"));
                            }
                            $i++;
                        }

                        if ($msg != "") {
                            $this->session->set_flashdata('msg', $msg);
                            $this->session->set_flashdata('error', $this->lang->line('bulkimport_error'));
                            redirect(base_url("bulkimport/index"));
                        }

                        $this->session->set_flashdata('success', $this->lang->line('bulkimport_success'));
                        redirect(base_url("bulkimport/index"));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('bulkimport_data_not_found'));
                        redirect(base_url("bulkimport/index"));
                    }
                } else {
                    $this->session->set_flashdata('error', "Wrong csv file!");
                    redirect(base_url("bulkimport/index"));
                }
            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('bulkimport_select_file'));
            redirect(base_url("bulkimport/index"));
        }

    }

    // Single  Validation Check
    private function singleQuestionCheck($array)
    {
        $groupID = $this->trim_required_exists_Check($array['question_group'], 'group');
        $levelID = $this->trim_required_exists_Check($array['difficulty_level'], 'level');
        $question = $this->trim_required_string_maxlength_minlength_Check($array['question'], 200);
        $explanation = $this->trim_string_maxlength_minlength_Check($array['explanation'], 200);
        $hints = $this->trim_string_maxlength_minlength_Check($array['hints'], 200);
        $mark = $this->trim_required_string_maxlength_minlength_Check($array['mark'], 40);
        $typeNumber = $this->trim_required_exists_Check($array['question_type'], 'type');
        $totalOption = $this->trim_int_maxlength_minlength_Check($array['total_option'], 10, 1);
        $options = $this->trim_unique_required_string_maxlength_minlength_Check($array['options'], $array, 'options', 10, 1);
        $correctAnswers = $this->trim_unique_required_string_maxlength_minlength_Check($array['correct_answer'], $array, 'answers', 10, 1);
        $retArray['status'] = true;
        if ($groupID && $levelID && $question && $explanation && $hints && $mark && $typeNumber && $totalOption && $options && $correctAnswers) {
            $retArray['status'] = true;
        } else {
            $retArray['status'] = false;
            if (!$groupID) {
                $retArray['error']['groupID'] = 'Invalid Group';
            }
            if (!$levelID) {
                $retArray['error']['levelID'] = 'Invalid Level';
            }
            if (!$question) {
                $retArray['error']['question'] = 'Invalid Question';
            }
            if (!$explanation) {
                $retArray['error']['explanation'] = 'Invalid Explanation';
            }
            if (!$hints) {
                $retArray['error']['hints'] = 'Invalid hints';
            }
            if (!$mark) {
                $retArray['error']['mark'] = 'Invalid Marking';
            }
            if (!$typeNumber) {
                $retArray['error']['typeNumber'] = 'Invalid Question Type';
            }
            if (!$totalOption) {
                $retArray['error']['totalOption'] = 'Invalid Total Option';
            }
            if (!$options) {
                $retArray['error']['options'] = 'Invalid Options';
            }
            if (!$correctAnswers) {
                $retArray['error']['correctAnswers'] = 'Invalid Correct Answers';
            }

        }
        return $retArray;
    }

    //trim check

    private function trim_required_exists_Check($data, $type)
    {
        $data = trim((string) $data);
        if ($data == '') {
            return false;
        }
        return $this->get_id($data, $type);
    }

    // Default Function All Import Validation Check
    public function arrayToPost($data)
    {
        if (is_array($data)) {
            $post = [];
            foreach ($data as $key => $item) {
                $key = preg_replace('/\s+/', '_', $key);
                $key = strtolower($key);
                $post[$key] = $item;
            }
            return $post;
        }
        return [];
    }

    private function trim_required_string_maxlength_minlength_Check($data, $maxlength = 10, $minlength = 0)
    {
        $data = (string) trim((string) $data);
        $dataLength = strlen($data);
        if (($dataLength == 0) || ($dataLength > $maxlength) || ($dataLength < $minlength)) {
            return false;
        }
        return $data;
    }

    private function trim_string_maxlength_minlength_Check($data, $maxlength = 10)
    {
        $data = (string) trim((string) $data);
        $dataLength = strlen($data);
        return !($dataLength > $maxlength);
    }

    private function trim_int_maxlength_minlength_Check($data, $maxlength = 10, $minlength = 1)
    {
        $data = (int) trim((string) $data);
        $dataLength = strlen($data);

        if (($dataLength > $maxlength) || ($dataLength < $minlength)) {
            return false;
        } else {
            if (is_int($data)) {
                return $data;
            }
            return false;
        }
    }

    private function trim_unique_required_string_maxlength_minlength_Check($data, $array, $type, $maxlength = 10, $minlength = 1)
    {

        $data = trim((string) $data);
        if ($data === '') {
            return false;
        }

        //newArray is either options or answers
        $newArray = explode(',', $data);

        if (customCompute($newArray) > $maxlength) {
            return false;
        }

        $totalOption = $array['total_option'];
        $questionType = $this->get_id($array['question_type'], 'type');
        if ($type == 'options') {
            if ((customCompute($newArray) != (int) $totalOption) || (customCompute($newArray) !== customCompute(array_unique($newArray)))) {
                return false;
            }
        } elseif ($questionType == 1) {
            if (customCompute($newArray) > 1) {
                return false;
            }
        } elseif ($questionType == 2) {
            if ((customCompute($newArray) !== customCompute(array_unique($newArray)) || ((customCompute($newArray)) > $totalOption))) {
                return false;
            }

        } elseif (customCompute($newArray) < 1) {
            return false;
        }
        return $data;
    }

    public function get_id($data, $type)
    {
        if ($type == 'group') {
            $group = $this->question_group_m->get_single_question_group(['title' => $data]);
            if ($group) {
                return $group->questionGroupID;
            }
            return false;
        } elseif ($type == 'level') {
            $level = $this->question_level_m->get_single_question_level(['name' => $data]);
            if ($level) {
                return $level->questionLevelID;
            }
            return false;

        } else {
            $type = $this->question_type_m->get_single_question_type(['name' => $data]);
            if ($type) {
                return $type->typeNumber;
            }
            return false;
        }
    }
}
