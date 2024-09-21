<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;;

class Idcardreport extends Admin_Controller
{

    public $load;
    public $session;
    public $lang;
    public $form_validation;
    public $input;
    public $section_m;
    public $student_m;
    public $systemadmin_m;
    public $question_level_m;
    public $question_level_report_m;
    public $online_exam_question_m;
    public $online_exam_user_answer_m;
    public $online_exam_user_answer_option_m;
    public $teacher_m;
    public $user_m;
    public $data;
    public $uri;
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
    | WEBSITE:            http://iNilabs.net
    | -----------------------------------------------------
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('usertype_m');
        $this->load->model('classes_m');
        $this->load->model('section_m');
        $this->load->model('user_m');
        $this->load->model('systemadmin_m');
        $this->load->model('student_m');
        $this->load->model('teacher_m');
        $this->load->model('schoolyear_m');
        $this->load->model('online_exam_question_m');
        $this->load->model('online_exam_user_answer_m');
        $this->load->model('online_exam_user_answer_option_m');
        $this->load->model('question_level_m');
        $this->load->model('question_level_report_m');
        $language = $this->session->userdata('lang');
        $this->lang->load('idcardreport', $language);
    }

    protected function rules($usertypeID)
    {
        $rules = array(
            array(
                'field' => 'usertypeID',
                'label' => $this->lang->line('idcardreport_idcard'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data',
            ),
            array(
                'field' => 'userID',
                'label' => $this->lang->line('idcardreport_user'),
                'rules' => 'trim|xss_clean|numeric',
            ),
            array(
                'field' => 'type',
                'label' => $this->lang->line('idcardreport_type'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data',
            ),
            array(
                'field' => 'background',
                'label' => $this->lang->line('idcardreport_background'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data',
            ),
        );

        if ($usertypeID == 3) {
            $rules[] = array(
                'field' => 'classesID',
                'label' => $this->lang->line('idcardreport_class'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data',
            );

            $rules[] = array(
                'field' => 'sectionID',
                'label' => $this->lang->line('idcardreport_section'),
                'rules' => 'trim|xss_clean|greater_than_equal_to[0]',
            );
        }
        return $rules;
    }

    protected function send_pdf_to_mail_rules($usertypeID)
    {
        $rules = array(
            array(
                'field' => 'usertypeID',
                'label' => $this->lang->line('idcardreport_idcard'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data',
            ),
            array(
                'field' => 'userID',
                'label' => $this->lang->line('idcardreport_user'),
                'rules' => 'trim|xss_clean|numeric',
            ),
            array(
                'field' => 'type',
                'label' => $this->lang->line('idcardreport_type'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data',
            ),
            array(
                'field' => 'background',
                'label' => $this->lang->line('idcardreport_background'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data',
            ),
            array(
                'field' => 'to',
                'label' => $this->lang->line('idcardreport_to'),
                'rules' => 'trim|required|xss_clean|valid_email',
            ),
            array(
                'field' => 'subject',
                'label' => $this->lang->line('idcardreport_subject'),
                'rules' => 'trim|required|xss_clean',
            ),
            array(
                'field' => 'message',
                'label' => $this->lang->line('idcardreport_message'),
                'rules' => 'trim|xss_clean',
            ),
        );
        if ($usertypeID == 3) {
            $rules[] = array(
                'field' => 'classesID',
                'label' => $this->lang->line('idcardreport_class'),
                'rules' => 'trim|required|xss_clean|numeric|callback_unique_data',
            );

            $rules[] = array(
                'field' => 'sectionID',
                'label' => $this->lang->line('idcardreport_section'),
                'rules' => 'trim|xss_clean|greater_than_equal_to[0]',
            );
        }
        return $rules;
    }

    public function unique_data($data)
    {
        if ($data != '') {
            if ($data == 0) {
                $this->form_validation->set_message('unique_data', 'The %s field is required.');
                return false;
            }
            return true;
        }
        return true;
    }

    public function getSection()
    {
        $classesID = $this->input->post('classesID');
        if ((int) $classesID !== 0) {
            $sections = $this->section_m->get_order_by_section(array('classesID' => $classesID));
            echo "<option value='0'>" . $this->lang->line("idcardreport_please_select") . "</option>";
            if (inicompute($sections)) {
                foreach ($sections as $section) {
                    echo "<option value='" . $section->sectionID . "'>" . $section->section . "</option>";
                }
            }
        }
    }

    public function getStudentByClass()
    {
        $usertypeID = $this->input->post('usertypeID');
        $classesID = $this->input->post('classesID');
        if (((int) $usertypeID && $usertypeID == 3) && ((int) $classesID && $classesID > 0)) {
            $queryArray['schoolyearID'] = $this->session->userdata('defaultschoolyearID');
            $queryArray['classesID'] = $classesID;
            $users = $this->student_m->get_order_by_student($queryArray);
            if (inicompute($users)) {
                echo "<option value='0'>" . $this->lang->line("idcardreport_please_select") . "</option>";
                foreach ($users as $user) {
                    echo "<option value='" . $user->studentID . "'>" . $user->name . "</option>";
                }
            }
        }
    }

    public function getStudentBySection()
    {
        $usertypeID = $this->input->post('usertypeID');
        $classesID = $this->input->post('classesID');
        $sectionID = $this->input->post('sectionID');
        if (((int) $usertypeID && $usertypeID == 3) && ((int) $classesID && $classesID > 0)) {
            $queryArray['schoolyearID'] = $this->session->userdata('defaultschoolyearID');
            $queryArray['classesID'] = $classesID;
            if ((int) $sectionID && $sectionID > 0) {
                $queryArray['sectionID'] = $sectionID;
            }
            $users = $this->student_m->get_order_by_student($queryArray);
            if (inicompute($users)) {
                echo "<option value='0'>" . $this->lang->line("idcardreport_please_select") . "</option>";
                foreach ($users as $user) {
                    echo "<option value='" . $user->studentID . "'>" . $user->name . "</option>";
                }
            }
        }
    }

    public function getUser()
    {
        $usertypeID = $this->input->post('usertypeID');
        $classesID = $this->input->post('classesID');
        $sectionID = $this->input->post('sectionID');

        if ((int) $usertypeID && ((int) $classesID || $classesID == 0) && ((int) $sectionID || $sectionID == 0)) {
            echo "<option value='0'>" . $this->lang->line("idcardreport_please_select") . "</option>";
            if ($usertypeID == 1) {
                $users = $this->systemadmin_m->get_systemadmin();
                if (inicompute($users)) {
                    foreach ($users as $user) {
                        echo "<option value='" . $user->systemadminID . "'>" . $user->name . "</option>";
                    }
                }
            } elseif ($usertypeID == 2) {
                $users = $this->teacher_m->get_teacher();
                if (inicompute($users)) {
                    foreach ($users as $user) {
                        echo "<option value='" . $user->teacherID . "'>" . $user->name . "</option>";
                    }
                }
            } elseif ($usertypeID == 3) {
                $users = [];
            } elseif ($usertypeID == 4) {
                $users = [];
            } else {
                $users = $this->user_m->get_order_by_user(array('usertypeID' => $usertypeID));
                if (inicompute($users)) {
                    foreach ($users as $user) {
                        echo "<option value='" . $user->userID . "'>" . $user->name . "</option>";
                    }
                }
            }
        }
    }

    private function queryArray($posts)
    {
        $usertypeID = $posts['usertypeID'];
        $classesID = $posts['classesID'];
        $sectionID = $posts['sectionID'];
        $userID = $posts['userID'];

        $queryArray = [];
        if ($usertypeID == 1) {
            if ($userID > 0) {
                $queryArray['systemadminID'] = $userID;
            }
            $users = $this->systemadmin_m->get_order_by_systemadmin($queryArray);
        } elseif ($usertypeID == 2) {
            if ($userID > 0) {
                $queryArray['teacherID'] = $userID;
            }
            $users = $this->teacher_m->get_order_by_teacher($queryArray);
        } elseif ($usertypeID == 3) {
            $queryArray['schoolyearID'] = $this->session->userdata('defaultschoolyearID');
            $queryArray['classesID'] = $classesID;
            if ($sectionID > 0) {
                $queryArray['sectionID'] = $sectionID;
            }
            if ($userID > 0) {
                $queryArray['studentID'] = $userID;
            }
            $users = $this->student_m->get_order_by_student($queryArray);
        } elseif ($usertypeID == 4) {
            $users = [];
        } else {
            $queryArray['usertypeID'] = $usertypeID;
            if ($userID > 0) {
                $queryArray['userID'] = $userID;
            }
            $users = $this->user_m->get_order_by_user($queryArray);
        }
        return $users;
    }

    public function index()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js' => array(
                'assets/select2/select2.js',
            ),
        );
        $this->data['usertypes'] = $this->usertype_m->get_usertype();
        $users = $this->student_m->get_student();
        $datareport = $this->question_level_report_m->report_subtype();
        $dataReportType = $this->question_level_report_m->report_type();
        $dataEndReport = $this->question_level_report_m->end_report();
        $dataTotalReport = $this->question_level_report_m->total_report();
        $dataEndReportLimit2 = $this->question_level_report_m->report_type_limit2();
        $types = $this->question_level_m->get_question_level();

        $results = $this->question_level_report_m->get_parent();
        foreach ($results as $key => &$parent) {
            $childs = $this->question_level_report_m->get_child($parent['userID'], $parent['examID'], $parent['ids']);
            $score = 0;
            foreach ($childs as $key => $child) {
                $parent['detail'][$child['title']][] = $child;
                $score += $child['mark'];
            }
            $parent['score'] = $score;
        }

        $this->data['subtype'] = $types;
        $this->data['classes'] = $this->classes_m->get_classes();
        $this->data['results'] = $results;
        $this->data["subview"] = "report/idcard/IdcardReportView";
        $this->load->view('_layout_main', $this->data);
    }

    public function index_old()
    {
        $this->data['headerassets'] = array(
            'css' => array(
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ),
            'js' => array(
                'assets/select2/select2.js',
            ),
        );
        $this->data['usertypes'] = $this->usertype_m->get_usertype();
        $users = $this->student_m->get_student();
        $datareport = $this->question_level_report_m->report_subtype();
        $dataReportType = $this->question_level_report_m->report_type();
        $dataEndReport = $this->question_level_report_m->end_report();
        $dataTotalReport = $this->question_level_report_m->total_report();
        $dataEndReportLimit2 = $this->question_level_report_m->report_type_limit2();
        $types = $this->question_level_m->get_question_level();
        $results = [];
        foreach ($users as $user) {
            $query = "SELECT
                        userID,
                        groupID,
                        questionLevelID,
                        AVG(value) AS value,
                        question_group.title AS name
                      FROM
                        question_level_report
                      JOIN question_group ON question_level_report.groupID = question_group.questionGroupID
                      WHERE userID = ?
                      GROUP BY
                        userID,
                        groupID,
                        questionLevelID;";

            $result = $this->db->query($query, array($user->studentID));
            $datareportSubtype = $result->result_array();

            $queryDetail = "SELECT 
                            a.optionID, 
                            a.userId, 
                            a.relasi_jabatan, 
                            c.groupID, 
                            AVG(b.nilaijawaban) AS value, 
                            d.title,
                            e.levelID                                        
                          FROM 
                            online_exam_user_answer_option a
                          JOIN 
                            question_option b ON a.optionID = b.optionID
                          JOIN 
                            online_exam c ON a.onlineExamID = c.onlineExamID
                          JOIN 
                            question_group d ON c.groupID = d.questionGroupID
                          JOIN 
                            question_bank e ON b.questionID = e.questionBankID
                          WHERE 
                            a.relasi_jabatan = ?
                          GROUP BY e.levelID, a.userID";

            $resultDetail = $this->db->query($queryDetail, array($user->studentID));
            $dataReportDetail = $resultDetail->result_array();

            $user_data = [
                'name' => $user->name,
                'types' => [],
                'result_test' => [],
            ];

            // Loop untuk setiap tipe pertanyaan
            foreach ($types as $type) {
                $value = 0;

                // Loop untuk setiap data laporan
                foreach ($dataReportType as $report) {
                    if ($report['userID'] == $user->studentID && $report['questionLevelID'] == $type->questionLevelID) {
                        $value = $report['value'];
                        break;
                    }
                }
                // Tambahkan data tipe ke dalam user_data
                $user_data['types'][] = [
                    'name' => $type->name,
                    'value' => $value,
                ];
            }
            $totalValue = array_sum(array_column($user_data['types'], 'value'));
            $count = count($user_data['types']);
            $positiveValueCount = count(array_filter($user_data['types'], function ($type) {
                return $type['value'] > 0;
            }));
            $averageValue = 0;
            if ($positiveValueCount != 0) {
                $averageValue = $count > 0 ? $totalValue / $positiveValueCount : 0;
            }

            $user_data['summary'][] = [
                'total' => $totalValue,
                'average' => $averageValue
            ];

            $arrayAssociative = [];



            // Loop through the results and build the associative array
            foreach ($dataReportDetail as $row) {
                $title = $row['title'];
                $userId = $row['userId'];
                $value = $row['value'];

                // Check if the title key exists in the associative array
                if (!isset($arrayAssociative[$title])) {
                    $arrayAssociative[$title] = [];
                }

                // Check if the user ID key exists for the current title
                if (!isset($arrayAssociative[$title][$userId])) {
                    // If user ID doesn't exist, initialize the user ID array as an empty array
                    $arrayAssociative[$title][$userId] = [];
                }

                // Append the value to the user ID array for the current title
                $arrayAssociative[$title][$userId][] = $value;
            }

            // Ensure each title group in the associative array has the right number of values
            $requiredResultsCount = count($types);
            foreach ($arrayAssociative as &$userEntries) {
                foreach ($userEntries as &$entry) {
                    // Get the current count of values
                    $currentCount = count($entry);

                    // Check if the current count is less than the required count
                    if ($currentCount < $requiredResultsCount) {
                        // Append zeros to the value array
                        for ($i = 0; $i < ($requiredResultsCount - $currentCount); $i++) {
                            $entry[] = 0;  // Add zero
                        }
                    }
                }
            }

            // Calculate totals and averages for each entry in the associative array
            foreach ($arrayAssociative as &$penilaian) {
                foreach ($penilaian as &$values) {
                    $totalValue = array_sum($values); // Hitung total dari semua nilai
                    $count = count($values); // Hitung jumlah nilai

                    $positiveValueCount = count(array_filter($values, function ($value) {
                        return $value > 0;
                    }));

                    $averageValue = 0;
                    if ($positiveValueCount != 0) {
                        $averageValue = $positiveValueCount > 0 ? $totalValue / $positiveValueCount : 0;
                    }

                    // Hitung rata-rata hanya jika ada nilai positif

                    // Tambahkan total dan average ke dalam array
                    $values['total'] = $totalValue; // Menambahkan total ke akhir array
                    $values['average'] = number_format($averageValue, 4); // Menambahkan average ke akhir array
                }
            }

            // Process dataPenilaian based on $datareportSubtype
            $dataPenilaian = [];
            foreach ($datareportSubtype as $data) {
                // Check if groupID already exists
                if (!isset($dataPenilaian[$data['groupID']])) {
                    // If groupID doesn't exist, create a new entry for it
                    $dataPenilaian[$data['groupID']] = [
                        'name' => $data['name'],  // Save name under groupID
                        'value' => [],
                        'detail' => []             // Initialize array for values
                    ];
                }

                // Insert value into the array for groupID
                $dataPenilaian[$data['groupID']]['value'][] = $data['value'];
            }

            // Ensure each group in $dataPenilaian has the right number of values
            foreach ($dataPenilaian as &$entry) {
                $currentCount = count($entry['value']);
                if ($currentCount < $requiredResultsCount) {
                    // Append zeros to the value array
                    for ($i = 0; $i < ($requiredResultsCount - $currentCount); $i++) {
                        $entry['value'][] = 0;  // Add zero
                    }
                }
            }

            // Calculate totals and averages for each group in dataPenilaian
            foreach ($dataPenilaian as &$penilaian) {
                $totalValue = array_sum($penilaian['value']); // Hitung total
                $count = count($penilaian['value']); // Hitung jumlah nilai

                $positiveValueCount = count(array_filter($penilaian['value'], function ($value) {
                    return $value > 0;
                }));

                $averageValue = 0;
                if ($positiveValueCount != 0) {
                    $averageValue = $positiveValueCount > 0 ? $totalValue / $positiveValueCount : 0;
                }

                // Hitung rata-rata jika ada nilai

                // Tambahkan total dan average ke dalam array
                $penilaian['total'] = $totalValue;
                $penilaian['average'] = number_format($averageValue, 4);
            }

            // Map detail to dataPenilaian based on arrayAssociative
            foreach ($dataPenilaian as $groupId => $groupData) {
                if (isset($arrayAssociative[$groupData['name']])) {
                    $dataPenilaian[$groupId]['detail'] = $arrayAssociative[$groupData['name']];
                }
            }

            // Process dataEndReportLimit2 to populate result_test
            foreach ($dataEndReportLimit2 as $data) {
                if ($data['userID'] == $user->studentID && $data['questionLevelID'] == $type->questionLevelID) {
                    $examIDExists = false;
                    foreach ($user_data['result_test'] as &$exam) {
                        if ($exam['examID'] == $data['examID']) {
                            $exam['results'][] = [
                                'value_tes' => $data['value'],
                                'questionLevelID' => $data['questionLevelID'],
                            ];
                            $examIDExists = true;
                            break;
                        }
                    }

                    // If examID doesn't exist, create a new entry
                    if (!$examIDExists) {
                        $user_data['result_test'][] = [
                            'name' => 'Hasil Exam',
                            'examID' => $data['examID'],
                            'results' => [[
                                'value_tes' => $data['value'],
                                'questionLevelID' => $data['questionLevelID'],
                            ]],
                        ];
                    }
                }
            }

            // Calculate totals and averages while constructing the summary
            foreach ($user_data['result_test'] as &$exam) {
                $totalValue = array_sum(array_column($exam['results'], 'value_tes'));
                // var_dump($totalValue);
                // exit;
                $count = count($exam['results']);

                $positiveValueCount = count(array_filter($exam['results'], function ($value) {
                    return $value > 0;
                }));

                $averageValue = 0;
                if ($positiveValueCount != 0) {
                    $averageValue = $positiveValueCount > 0 ? $totalValue / $positiveValueCount : 0;
                }
                // $averageValue = $count > 0 ? $totalValue / $count : 0;

                // Add summary directly to the exam
                $exam['summary'] = [
                    'total' => $totalValue,
                    'average' => number_format($averageValue, 4),
                ];

                // Ensure each examID has the correct number of results
                $currentResultsCount = count($exam['results']);
                if ($currentResultsCount < count($types)) {
                    foreach ($types as $type) {
                        if (!in_array($type->questionLevelID, array_column($exam['results'], 'questionLevelID'))) {
                            $exam['results'][] = [
                                'value_tes' => 0,
                                'questionLevelID' => $type->questionLevelID,
                            ];
                        }
                    }
                }
            }

            // Set final scores based on dataEndReport and dataTotalReport
            foreach ($dataEndReport as $nilaiakhir) {
                if ($nilaiakhir['userID'] == $user->studentID) {
                    $user_data['nilai_akhir'] = $nilaiakhir['nilai_akhir'];
                    break;
                }
            }

            foreach ($dataTotalReport as $totalnilaiakhir) {
                if ($totalnilaiakhir['userID'] == $user->studentID) {
                    $user_data['total_akhir'] = $totalnilaiakhir['total_akhir'];
                    break;
                }
            }


            // Add penilaian to the user data
            $user_data['result_test']['penilaian'] = $dataPenilaian;

            // Add user_data to final results
            $results[] = $user_data;
        }
        $this->data['subtype'] = $types;
        $this->data['classes'] = $this->classes_m->get_classes();
        $this->data['results'] = $results;
        $this->data["subview"] = "report/idcard/IdcardReportView";
        $this->load->view('_layout_main', $this->data);
    }


    public function getReport()
    {
        try {
            $users = $this->student_m->get_student();
            $dataReportType = $this->question_level_report_m->report_type();
            $dataEndReport = $this->question_level_report_m->end_report();
            $dataEndReportLimit2 = $this->question_level_report_m->report_type_limit2();
            $dataTotalReport = $this->question_level_report_m->total_report();
            $types = $this->question_level_m->get_question_level();

            $results = [];
            foreach ($users as $user) {
                $query = "SELECT
                    userID,
                    groupID,
                    questionLevelID,
                    AVG(value) AS value,
                    question_group.title AS name
                  FROM
                    question_level_report
                  JOIN question_group ON question_level_report.groupID = question_group.questionGroupID
                  WHERE userID = ?
                  GROUP BY
                    userID,
                    groupID,
                    questionLevelID;";

                $result = $this->db->query($query, array($user->studentID));
                $datareportSubtype = $result->result_array();

                $queryDetail = "SELECT 
                        a.optionID, 
                        a.userId, 
                        a.relasi_jabatan, 
                        c.groupID, 
                        AVG(b.nilaijawaban) AS value, 
                        d.title,
                        e.levelID                                        
                      FROM 
                        online_exam_user_answer_option a
                      JOIN 
                        question_option b ON a.optionID = b.optionID
                      JOIN 
                        online_exam c ON a.onlineExamID = c.onlineExamID
                      JOIN 
                        question_group d ON c.groupID = d.questionGroupID
                      JOIN 
                        question_bank e ON b.questionID = e.questionBankID
                      WHERE 
                        a.relasi_jabatan = ?
                      GROUP BY e.levelID, a.userID";

                $resultDetail = $this->db->query($queryDetail, array($user->studentID));
                $dataReportDetail = $resultDetail->result_array();

                $user_data = [
                    'name' => $user->name,
                    'types' => [],
                    'result_test' => [],
                ];

                // Loop untuk setiap tipe pertanyaan
                foreach ($types as $type) {
                    $value = 0;

                    // Loop untuk setiap data laporan
                    foreach ($dataReportType as $report) {
                        if ($report['userID'] == $user->studentID && $report['questionLevelID'] == $type->questionLevelID) {
                            $value = $report['value'];
                            break;
                        }
                    }
                    // Tambahkan data tipe ke dalam user_data
                    $user_data['types'][] = [
                        'name' => $type->name,
                        'value' => $value,
                    ];
                }
                $totalValue = array_sum(array_column($user_data['types'], 'value'));
                $count = count($user_data['types']);
                $positiveValueCount = count(array_filter($user_data['types'], function ($type) {
                    return $type['value'] > 0;
                }));
                $averageValue = 0;
                if ($positiveValueCount != 0) {
                    $averageValue = $count > 0 ? $totalValue / $positiveValueCount : 0;
                }

                $user_data['summary'][] = [
                    'total' => $totalValue,
                    'average' => $averageValue
                ];

                $arrayAssociative = [];



                // Loop through the results and build the associative array
                foreach ($dataReportDetail as $row) {
                    $title = $row['title'];
                    $userId = $row['userId'];
                    $value = $row['value'];

                    // Check if the title key exists in the associative array
                    if (!isset($arrayAssociative[$title])) {
                        $arrayAssociative[$title] = [];
                    }

                    // Check if the user ID key exists for the current title
                    if (!isset($arrayAssociative[$title][$userId])) {
                        // If user ID doesn't exist, initialize the user ID array as an empty array
                        $arrayAssociative[$title][$userId] = [];
                    }

                    // Append the value to the user ID array for the current title
                    $arrayAssociative[$title][$userId][] = $value;
                }

                // Ensure each title group in the associative array has the right number of values
                $requiredResultsCount = count($types);
                foreach ($arrayAssociative as &$userEntries) {
                    foreach ($userEntries as &$entry) {
                        // Get the current count of values
                        $currentCount = count($entry);

                        // Check if the current count is less than the required count
                        if ($currentCount < $requiredResultsCount) {
                            // Append zeros to the value array
                            for ($i = 0; $i < ($requiredResultsCount - $currentCount); $i++) {
                                $entry[] = 0;  // Add zero
                            }
                        }
                    }
                }

                // Calculate totals and averages for each entry in the associative array
                foreach ($arrayAssociative as &$penilaian) {
                    foreach ($penilaian as &$values) {
                        $totalValue = array_sum($values); // Hitung total dari semua nilai
                        $count = count($values); // Hitung jumlah nilai

                        $positiveValueCount = count(array_filter($values, function ($value) {
                            return $value > 0;
                        }));

                        $averageValue = 0;
                        if ($positiveValueCount != 0) {
                            $averageValue = $positiveValueCount > 0 ? $totalValue / $positiveValueCount : 0;
                        }

                        // Hitung rata-rata hanya jika ada nilai positif

                        // Tambahkan total dan average ke dalam array
                        $values['total'] = $totalValue; // Menambahkan total ke akhir array
                        $values['average'] = number_format($averageValue, 4); // Menambahkan average ke akhir array
                    }
                }

                // Process dataPenilaian based on $datareportSubtype
                $dataPenilaian = [];
                foreach ($datareportSubtype as $data) {
                    // Check if groupID already exists
                    if (!isset($dataPenilaian[$data['groupID']])) {
                        // If groupID doesn't exist, create a new entry for it
                        $dataPenilaian[$data['groupID']] = [
                            'name' => $data['name'],  // Save name under groupID
                            'value' => [],
                            'detail' => []             // Initialize array for values
                        ];
                    }

                    // Insert value into the array for groupID
                    $dataPenilaian[$data['groupID']]['value'][] = $data['value'];
                }

                // Ensure each group in $dataPenilaian has the right number of values
                foreach ($dataPenilaian as &$entry) {
                    $currentCount = count($entry['value']);
                    if ($currentCount < $requiredResultsCount) {
                        // Append zeros to the value array
                        for ($i = 0; $i < ($requiredResultsCount - $currentCount); $i++) {
                            $entry['value'][] = 0;  // Add zero
                        }
                    }
                }

                // Calculate totals and averages for each group in dataPenilaian
                foreach ($dataPenilaian as &$penilaian) {
                    $totalValue = array_sum($penilaian['value']); // Hitung total
                    $count = count($penilaian['value']); // Hitung jumlah nilai

                    $positiveValueCount = count(array_filter($penilaian['value'], function ($value) {
                        return $value > 0;
                    }));

                    $averageValue = 0;
                    if ($positiveValueCount != 0) {
                        $averageValue = $positiveValueCount > 0 ? $totalValue / $positiveValueCount : 0;
                    }

                    // Hitung rata-rata jika ada nilai

                    // Tambahkan total dan average ke dalam array
                    $penilaian['total'] = $totalValue;
                    $penilaian['average'] = number_format($averageValue, 4);
                }

                // Map detail to dataPenilaian based on arrayAssociative
                foreach ($dataPenilaian as $groupId => $groupData) {
                    if (isset($arrayAssociative[$groupData['name']])) {
                        $dataPenilaian[$groupId]['detail'] = $arrayAssociative[$groupData['name']];
                    }
                }

                // Process dataEndReportLimit2 to populate result_test
                foreach ($dataEndReportLimit2 as $data) {
                    if ($data['userID'] == $user->studentID && $data['questionLevelID'] == $type->questionLevelID) {
                        $examIDExists = false;
                        foreach ($user_data['result_test'] as &$exam) {
                            if ($exam['examID'] == $data['examID']) {
                                $exam['results'][] = [
                                    'value_tes' => $data['value'],
                                    'questionLevelID' => $data['questionLevelID'],
                                ];
                                $examIDExists = true;
                                break;
                            }
                        }

                        // If examID doesn't exist, create a new entry
                        if (!$examIDExists) {
                            $user_data['result_test'][] = [
                                'name' => 'Hasil Exam',
                                'examID' => $data['examID'],
                                'results' => [[
                                    'value_tes' => $data['value'],
                                    'questionLevelID' => $data['questionLevelID'],
                                ]],
                            ];
                        }
                    }
                }

                // Calculate totals and averages while constructing the summary
                foreach ($user_data['result_test'] as &$exam) {
                    $totalValue = array_sum(array_column($exam['results'], 'value_tes'));
                    // var_dump($totalValue);
                    // exit;
                    $count = count($exam['results']);

                    $positiveValueCount = count(array_filter($exam['results'], function ($value) {
                        return $value > 0;
                    }));

                    $averageValue = 0;
                    if ($positiveValueCount != 0) {
                        $averageValue = $positiveValueCount > 0 ? $totalValue / $positiveValueCount : 0;
                    }
                    // $averageValue = $count > 0 ? $totalValue / $count : 0;

                    // Add summary directly to the exam
                    $exam['summary'] = [
                        'total' => $totalValue,
                        'average' => number_format($averageValue, 4),
                    ];

                    // Ensure each examID has the correct number of results
                    $currentResultsCount = count($exam['results']);
                    if ($currentResultsCount < count($types)) {
                        foreach ($types as $type) {
                            if (!in_array($type->questionLevelID, array_column($exam['results'], 'questionLevelID'))) {
                                $exam['results'][] = [
                                    'value_tes' => 0,
                                    'questionLevelID' => $type->questionLevelID,
                                ];
                            }
                        }
                    }
                }

                // Set final scores based on dataEndReport and dataTotalReport
                foreach ($dataEndReport as $nilaiakhir) {
                    if ($nilaiakhir['userID'] == $user->studentID) {
                        $user_data['nilai_akhir'] = $nilaiakhir['nilai_akhir'];
                        break;
                    }
                }

                foreach ($dataTotalReport as $totalnilaiakhir) {
                    if ($totalnilaiakhir['userID'] == $user->studentID) {
                        $user_data['total_akhir'] = $totalnilaiakhir['total_akhir'];
                        break;
                    }
                }


                // Add penilaian to the user data
                $user_data['result_test']['penilaian'] = $dataPenilaian;

                // Add user_data to final results
                $results[] = $user_data;
            }
            // Create a new Spreadsheet object
            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set header row
            $sheet->setCellValue('A1', 'No');
            $sheet->setCellValue('B1', 'Nama');

            // Set subtype headers
            $col = 'C';
            foreach ($types as $type) {
                $sheet->setCellValue($col . '1', $type->name);
                $col++;
            }

            // Add header for Total Akhir and Nilai Akhir
            $sheet->setCellValue($col . '1', 'Total Akhir');
            $col++; // Move to the next column
            $sheet->setCellValue($col . '1', 'Nilai Akhir');
            $row = 2; // Starting row for data

            // Set data rows
            foreach ($results as $i => $result) {
                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, $result['name']);

                // Set values for types
                $col = 'C';
                foreach ($result['types'] as $type) {
                    $sheet->setCellValue($col . $row, $type['value']);
                    $col++;
                }

                // Set total akhir
                $sheet->setCellValue($col . $row, round($result['summary'][0]['total'], 2));
                $col++; // Move to the next column for nilai akhir

                // Set nilai akhir
                $sheet->setCellValue($col . $row, round($result['summary'][0]['average'], 2));

                // Increment row for the next entry
                $row++;

                // Nested table (result_test)
                foreach ($result['result_test'] as $resultTes) {
                    if (!empty($resultTes['results'])) {
                        // Add a new row for the result test
                        $sheet->setCellValue('A' . $row, ''); // No for nested table (optional)
                        $sheet->setCellValue('B' . $row, $resultTes['name'] === "Atasan" ? "Bawahan" : ($resultTes['name'] === "Bawahan" ? "Atasan" : $resultTes['name']));
                        $col = 'C';
                        foreach ($resultTes['results'] as $hasil) {
                            $sheet->setCellValue($col . $row, $hasil['value_tes']);
                            $col++;
                        }
                        // Add summary for the test
                        // $sheet->setCellValue($col . $row, round($resultTes['summary']['total'], 2)); // Total
                        // $col++;
                        // $sheet->setCellValue($col . $row, round($resultTes['summary']['average'], 2)); // Average
                        // $row++;
                    }
                }

                // Penilaian
                if (!empty($result['result_test']['penilaian'])) {
                    foreach ($result['result_test']['penilaian'] as $hasilpenilaian) {
                        // Add a new row for the total of penilaian
                        $sheet->setCellValue('A' . $row, ''); // No for total
                        $sheet->setCellValue('B' . $row, 'Total ' . ($hasilpenilaian['name'] === "Atasan" ? "Bawahan" : ($hasilpenilaian['name'] === "Bawahan" ? "Atasan" : $hasilpenilaian['name'])));
                        $col = 'C';

                        foreach ($hasilpenilaian['value'] as $value) {
                            $sheet->setCellValue($col . $row, round($value, 2));
                            $col++;
                        }

                        // Add total and average for penilaian
                        $sheet->setCellValue($col . $row, round($hasilpenilaian['total'], 2)); // Total
                        $col++;
                        $sheet->setCellValue($col . $row, round($hasilpenilaian['average'], 2)); // Average
                        $row++;

                        $nomor = 1;
                        // Make sure to include only the necessary details
                        if (!empty($hasilpenilaian['detail'])) {
                            foreach ($hasilpenilaian['detail'] as $datadetail) {
                                $sheet->setCellValue('A' . $row, ''); // No for detail
                                $sheet->setCellValue('B' . $row, ($hasilpenilaian['name'] == "Atasan" ? "Bawahan" : ($hasilpenilaian['name'] == "Bawahan" ? "Atasan" : $hasilpenilaian['name'])) . ' ' . $nomor);
                                $col = 'C';
                                foreach ($datadetail as $value) {
                                    $sheet->setCellValue($col . $row, round($value, 2));
                                    $col++;
                                }
                                unset($hasilpenilaian['total']);
                                unset($hasilpenilaian['average']);
                                // var_dump($datadetail);
                                // exit;
                                // Add total and average for detail if needed
                                // $sheet->setCellValue($col . $row, round($hasilpenilaian['total'], 2)); // Total
                                $col++;
                                // $sheet->setCellValue($col . $row, round($hasilpenilaian['average'], 2)); // Average
                                $row++;
                                $nomor++;
                            }
                        }
                    }
                }
            }

            // Set column widths for better readability
            foreach (range('A', $col) as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            $filename = 'report.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');




            // $spreadsheet = new Spreadsheet();
            // $sheet = $spreadsheet->getActiveSheet();

            // // Set the headers
            // $rowIndex = 1;
            // $colIndex = 'A';

            // $sheet->setCellValue($colIndex . $rowIndex, 'Name');
            // $colIndex++;

            // foreach ($types as $type) {
            //     $sheet->setCellValue($colIndex . $rowIndex, $type->name);
            //     $colIndex++;
            // }

            // $sheet->setCellValue($colIndex . $rowIndex, 'Final Score');
            // $rowIndex++; // Move to the next row for data

            // // Loop through each user and their corresponding data
            // foreach ($results as $user_data) {
            //     $colIndex = 'A';
            //     $sheet->setCellValue($colIndex . $rowIndex, $user_data['name']);
            //     $colIndex++;

            //     // Fill type values
            //     foreach ($user_data['types'] as $type_data) {
            //         $sheet->setCellValue($colIndex . $rowIndex, $type_data['value']);
            //         $colIndex++;
            //     }

            //     // Add final score if available
            //     $sheet->setCellValue($colIndex . $rowIndex, round($user_data['nilai_akhir'], 2) ?? 0);
            //     $rowIndex++; // Move to the next row for result_test

            //     // Add result_test data
            //     $examData = []; // Array to hold exam data

            //     // Collect the exam data for comparison
            //     foreach ($user_data['result_test'] as $exam) {
            //         $examData[] = $exam; // Store each exam for later processing
            //     }

            //     // Sort exams by examID in ascending order
            //     usort($examData, function($a, $b) {
            //         return $a['examID'] <=> $b['examID']; // Ascending order
            //     });

            //     // Prepare to set values in the spreadsheet
            //     $totalScores = []; // Initialize an array to hold total scores

            //     foreach ($examData as $index => $exam) {
            //         $colIndex = 'A';

            //         // Set the Exam ID in the first column with proper labeling
            //         if ($index === 0) {
            //             $sheet->setCellValue($colIndex . $rowIndex, 'Exam Pertama: ID ' . $exam['examID']);
            //         } elseif ($index === 1) {
            //             $sheet->setCellValue($colIndex . $rowIndex, 'Exam Kedua: ID ' . $exam['examID']);
            //         }

            //         $colIndex++; // Move to the next column

            //         // Set values for this exam's results
            //         foreach ($exam['results'] as $result) {
            //             $value = round($result['value_tes'], 2) ?? 0;
            //             $sheet->setCellValue($colIndex . $rowIndex, $value);
            //             $totalScores[] = $value; // Accumulate total scores
            //             $colIndex++;
            //         }

            //         $rowIndex++; // Move to the next row for the next exam data

            //         // Add detailed results below each exam
            //         foreach ($exam['results'] as $result) {
            //             $colIndex = 'A'; // Reset column index for details
            //             $sheet->setCellValue($colIndex . $rowIndex, 'Detail:'); // Add label for Detail
            //             $colIndex++;

            //             // Assuming each result has a 'questionLevelID' for more details, adjust as necessary
            //             $sheet->setCellValue($colIndex . $rowIndex, 'Question Level ID: ' . $result['questionLevelID']);
            //             $colIndex++;

            //             // Add additional details if available
            //             $sheet->setCellValue($colIndex . $rowIndex, 'Score: ' . $value);
            //             $colIndex++;

            //             $rowIndex++; // Move to the next row for the next detail
            //         }

            //         // Limit to 2 rows for nested data
            //         if ($index >= 1) {
            //             break;
            //         }
            //     }

            //     // Add Total Row
            //     $colIndex = 'A'; // Reset column index for Total row
            //     $sheet->setCellValue($colIndex . $rowIndex, 'Total'); // Add label for Total
            //     $colIndex++;

            //     // Calculate and set total for each type of exam
            //     foreach ($totalScores as $score) {
            //         $sheet->setCellValue($colIndex . $rowIndex, round($score, 2)); // Set each total value
            //         $colIndex++;
            //     }

            //     $rowIndex++; // Move to the next row for the next user
            // }

            // // Output the Excel file
            // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            // header('Content-Disposition: attachment;filename="student_report.xlsx"');
            // header('Cache-Control: max-age=0');

            // $writer = new Xlsx($spreadsheet);
            // $writer->save('php://output');
            exit;
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            echo 'Error creating spreadsheet: ',  $e->getMessage();
        } catch (\Exception $e) {
            echo 'General error: ',  $e->getMessage();
        }
    }



    public function getIdcardReport()
    {
        $retArray['status'] = false;
        $retArray['render'] = '';
        if (permissionChecker('idcardreport')) {
            if ($_POST !== []) {
                $usertypeID = $this->input->post('usertypeID');
                $rules = $this->rules($usertypeID);
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == false) {
                    $retArray = $this->form_validation->error_array();
                    $retArray['status'] = false;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $schoolyearID = $this->session->userdata('defaultschoolyearID');
                    $this->data['usertypeID'] = $usertypeID;
                    $this->data['classesID'] = $this->input->post('classesID');
                    $this->data['sectionID'] = $this->input->post('sectionID');
                    $this->data['userID'] = $this->input->post('userID');
                    $this->data['type'] = $this->input->post('type');
                    $this->data['background'] = $this->input->post('background');
                    $this->data['schoolyear'] = $this->schoolyear_m->get_single_schoolyear(array('schoolyearID' => $schoolyearID));
                    $this->data['classes'] = pluck($this->classes_m->get_classes(), 'classes', 'classesID');
                    $this->data['sections'] = pluck($this->section_m->get_section(), 'section', 'sectionID');
                    $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
                    $this->data['idcards'] = $this->queryArray($this->input->post());
                    $retArray['status'] = true;
                    $retArray['render'] = $this->load->view('report/idcard/IdcardReport', $this->data, true);
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['status'] = true;
                $retArray['render'] = $this->load->view('report/reporterror', $this->data, true);
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['status'] = true;
            $retArray['render'] = $this->load->view('report/reporterror', $this->data, true);
            echo json_encode($retArray);
            exit;
        }
    }

    public function pdf()
    {
        if (permissionChecker('idcardreport')) {
            $usertypeID = htmlentities((string) escapeString($this->uri->segment(3)));
            $classesID = htmlentities((string) escapeString($this->uri->segment(4)));
            $sectionID = htmlentities((string) escapeString($this->uri->segment(5)));
            $userID = htmlentities((string) escapeString($this->uri->segment(6)));
            $type = htmlentities((string) escapeString($this->uri->segment(7)));
            $background = htmlentities((string) escapeString($this->uri->segment(8)));
            $schoolyearID = $this->session->userdata('defaultschoolyearID');

            if ((int) $usertypeID && ((int) $classesID || $classesID == 0) && ((int) $sectionID || $sectionID == 0) && ((int) $userID || $userID == 0) && (int) $type && (int) $background) {
                $this->data['usertypeID'] = $usertypeID;
                $this->data['classesID'] = $classesID;
                $this->data['sectionID'] = $sectionID;
                $this->data['userID'] = $userID;
                $this->data['type'] = $type;
                $this->data['background'] = $background;
                $this->data['schoolyear'] = $this->schoolyear_m->get_single_schoolyear(array('schoolyearID' => $schoolyearID));

                $this->data['classes'] = pluck($this->classes_m->get_classes(), 'classes', 'classesID');
                $this->data['sections'] = pluck($this->section_m->get_section(), 'section', 'sectionID');
                $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
                $array['usertypeID'] = $usertypeID;
                $array['classesID'] = $classesID;
                $array['sectionID'] = $sectionID;
                $array['userID'] = $userID;
                $this->data['idcards'] = $this->queryArray($array);
                $this->reportPDF('idcardreport.css', $this->data, 'report/idcard/IdcardReportPDF', 'view', 'a4');
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function send_pdf_to_mail()
    {
        $retArray['status'] = false;
        $retArray['message'] = '';

        if (permissionChecker('idcardreport')) {
            if ($_POST !== []) {
                $to = $this->input->post('to');
                $subject = $this->input->post('subject');
                $message = $this->input->post('message');
                $usertypeID = $this->input->post('usertypeID');
                $classesID = $this->input->post('classesID');
                $sectionID = $this->input->post('sectionID');
                $userID = $this->input->post('userID');
                $type = $this->input->post('type');
                $background = $this->input->post('background');
                $schoolyearID = $this->session->userdata('defaultschoolyearID');
                $rules = $this->send_pdf_to_mail_rules($usertypeID);
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == false) {
                    $retArray = $this->form_validation->error_array();
                    $retArray['status'] = false;
                    echo json_encode($retArray);
                    exit;
                } elseif ((int) $usertypeID && ((int) $classesID || $classesID == 0) && ((int) $sectionID || $sectionID == 0) && ((int) $userID || $userID == 0) && (int) $type && (int) $background) {
                    $this->data['usertypeID'] = $usertypeID;
                    $this->data['classesID'] = $classesID;
                    $this->data['sectionID'] = $sectionID;
                    $this->data['userID'] = $userID;
                    $this->data['type'] = $type;
                    $this->data['background'] = $background;
                    $this->data['schoolyear'] = $this->schoolyear_m->get_single_schoolyear(array('schoolyearID' => $schoolyearID));
                    $this->data['classes'] = pluck($this->classes_m->get_classes(), 'classes', 'classesID');
                    $this->data['sections'] = pluck($this->section_m->get_section(), 'section', 'sectionID');
                    $this->data['usertypes'] = pluck($this->usertype_m->get_usertype(), 'usertype', 'usertypeID');
                    $array['usertypeID'] = $usertypeID;
                    $array['classesID'] = $classesID;
                    $array['sectionID'] = $sectionID;
                    $array['userID'] = $userID;
                    $this->data['idcards'] = $this->queryArray($array);
                    $this->reportSendToMail('idcardreport.css', $this->data, 'report/idcard/IdcardReportPDF', $to, $subject, $message);
                    $retArray['message'] = "Message";
                    $retArray['status'] = true;
                    echo json_encode($retArray);
                    exit;
                } else {
                    $retArray['message'] = $this->lang->line('idcardreport_data_not_found');
                    echo json_encode($retArray);
                    exit;
                }
            } else {
                $retArray['message'] = $this->lang->line('idcardreport_permissionmethod');
                echo json_encode($retArray);
                exit;
            }
        } else {
            $retArray['message'] = $this->lang->line('idcardreport_permission');
            echo json_encode($retArray);
            exit;
        }
    }

    public function update_insert_question()
    {
        $data = $this->input->post('data');
        $userID       = $this->session->userdata("loginuserID");
        $response = [];
        // Dump => array(4) {
        //     [0] => array(4) {
        //       ["questionLevelReportID"] => string(2) "14"
        //       ["answer"] => string(1) "4"
        //       ["optionID"] => string(3) "114"
        //       ["answerID"] => string(3) "154"
        //     }
        //     [1] => array(4) {
        //       ["questionLevelReportID"] => string(2) "15"
        //       ["answer"] => string(1) "5"
        //       ["optionID"] => string(3) "115"
        //       ["answerID"] => string(3) "155"
        //     }
        //     [2] => array(4) {
        //       ["questionLevelReportID"] => string(2) "16"
        //       ["answer"] => string(2) "53"
        //       ["optionID"] => string(3) "116"
        //       ["answerID"] => string(3) "156"
        //     }
        //     [3] => array(4) {
        //       ["questionLevelReportID"] => string(2) "17"
        //       ["answer"] => string(2) "23"
        //       ["optionID"] => string(3) "117"
        //       ["answerID"] => string(3) "157"
        //     }
        //   }

        // insert into 
        $this->db->trans_begin();
        try {
            $answer_ids = $option_ids = [];
            $ref = uniqid();
            foreach ($data as $key => $value) {
                $report = $this->question_level_report_m->get_single_data([
                    'questionLevelReportID' => $value['questionLevelReportID']
                ]);

                // insert into online_exam_user_answer_m
                $dt = $this->online_exam_user_answer_m->get_single_online_exam_user_answer(
                    [
                        'onlineExamUserAnswerID' => $value['answerID']
                    ]
                );
                $this->online_exam_user_answer_m->insert([
                    'onlineExamQuestionID' => $dt->onlineExamQuestionID,
                    'userID'               => $userID,
                    'onlineExamID'         => $dt->onlineExamID,
                    'examtimeID'           => $dt->examtimeID,
                    'relasi_jabatan'       => $dt->relasi_jabatan,
                ]);
                $answer_ids[] = $this->db->insert_id();

                // insert into online_exam_user_answer_option_m
                $option = $this->online_exam_user_answer_option_m->get_single_online_exam_user_answer_option(
                    [
                        'onlineExamUserAnswerOptionID'=>$value['optionID']
                    ]
                );
                $this->online_exam_user_answer_option_m->insert([
                    'questionID'           => $option->questionID,
                    'typeID'               => $option->typeID,
                    'text'                 => $value['answer'],
                    'time'                 => date("Y-m-d h:i:s"),
                    'onlineExamID'         => $option->onlineExamID,
                    'examtimeID'           => $option->examtimeID,
                    'userID'               => $userID,
                    'relasi_jabatan'       => $option->relasi_jabatan,
                    'fileAnswer'           => $option->fileAnswer
                ]);
                $option_ids[] = $this->db->insert_id();
                

                // insert into question_level_report_m
                $this->question_level_report_m->insert([
                    'questionLevelID' => $report->questionLevelID,
                    'value' => $value['answer'],
                    'groupID' => $report->groupID,
                    'userID' => $userID,
                    'examID' => $report->examID,
                    'questionID' => $report->questionID,
                    'onlineExamUserAnswerOptionID' => $option_ids[$key],
                    'onlineExamUserAnswerID' => $answer_ids[$key],
                    'ref'=>$ref,
                ]);
            }
            
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                $response = [
                    'success'=>false,
                ];
            }else{
                $this->db->trans_commit();
            }
            $response = [
                'success'=>true,
            ];

        } catch (Exception $e) {
            // Catch any exceptions and rollback the transaction
            $this->db->trans_rollback();
            log_message('error', $e->getMessage()); // Log the error message for debugging
            $response = [
                'success'=>false,
                'message'=> $e->getMessage(),
            ];
        }

        echo json_encode($response);
        exit;
    }
}

/* End of file activities.php */
/* Location: .//D/xampp/htdocs/school/mvc/controllers/activities.php */
