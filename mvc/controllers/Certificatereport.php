<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Certificatereport extends Admin_Controller {
public $load;
 public $session;
 public $lang;
 public $data;
 public $form_validation;
 public $input;
 public $section_m;
 public $classes_m;
 public $studentrelation_m;
 public $uri;
 public $usertype_m;
 public $certificate_template_m;
 public $schoolyear_m;
 public $studentgroup_m;
 public $subject_m;
 public $mailandsmstemplatetag_m;
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
	function __construct() {
		parent::__construct();
		$this->load->model('section_m');
		$this->load->model("classes_m");
		$this->load->model("certificate_template_m");
		$this->load->model("studentrelation_m");
		$this->load->model("studentgroup_m");
		$this->load->model("subject_m");
		$this->load->model("laporan_bulanan");
		$this->load->model("mailandsmstemplatetag_m");
		$language = $this->session->userdata('lang');
		$this->lang->load('certificatereport', $language);
	}

	public function index() {
		$this->data['headerassets'] = array(
			'css' => array(
				'assets/select2/css/select2.css',
				'assets/select2/css/select2-bootstrap.css'
			),
			'js' => array(
				'assets/select2/select2.js'
			)
		);
		// if($_POST !== []) {
		// 	$this->form_validation->set_rules('name', 'Name', 'required');
            
        //     // Validasi untuk file (memastikan bahwa file sudah dipilih)
        //     if (empty($_FILES['file']['name'])) {
        //         $this->form_validation->set_rules('file', 'File', 'required');
        //     }

        //     // Jika validasi gagal
        //     if ($this->form_validation->run() == FALSE) {
		// 		// $this->session->set_flashdata('error', 'Gagal');
        //         redirect(base_url('certificatereport'));
		// 	} else {
		// 		// $this->session->set_flashdata('success', 'Sukses');		
		// 		redirect(base_url('certificatereport'));
		// 	}
		// }
		
		// dd($dataresultpertanyaan);
		$dataresultpertanyaan = null; // Initialize variable
		$result = [];
		$laps = [];
		if ($this->session->userdata('usertypeID') == 1){
			$this->db->select('laporan_bulanan.*, student.name as p_name'); // Replace '*' with the specific columns you need
			$this->db->from('laporan_bulanan'); // Replace 'users' with your table name
			$this->db->join('student', 'student.studentID = laporan_bulanan.create_userID','left'); // Replace 'users' with your table name
			// $this->db->where('laporan_bulanan.parent_id', $result[0]->parentsID);
			// $this->db->where('MONTH(laporan_bulanan.date)', date('m')); 
			// $this->db->where('YEAR(laporan_bulanan.date)', date('Y'));
			$subquery = $this->db->get();
			if ($subquery->num_rows() > 0) {
				$laps = $subquery->result();
			};
		} else{
			// $this->db->select('*'); // Replace '*' with the specific columns you need
			// $this->db->from('student'); // Replace 'users' with your table name
			// $this->db->join('parents', 'parents.parentsID = student.parentID', 'left');
			// $this->db->where('studentID', $this->session->userdata('loginuserID')); // Assuming 'id' is the column name for user IDs
			// $this->db->limit(1);
			// $query = $this->db->get();
			

			
			// Fetch the result from the first query
			// if ($query->num_rows() > 0) {
				// $result = $query->result();
			$this->db->select('laporan_bulanan.*, student.name as p_name'); // Replace '*' with the specific columns you need
			$this->db->from('laporan_bulanan'); // Replace 'users' with your table name
			$this->db->join('student', 'student.studentID = laporan_bulanan.create_userID','left'); // Replace 'users' with your table name
			$this->db->where('laporan_bulanan.create_userID', $this->session->userdata('loginuserID'));
			// $this->db->where('MONTH(laporan_bulanan.date)', date('m')); // Filter berdasarkan bulan ini
			// $this->db->where('YEAR(laporan_bulanan.date)', date('Y'));
			$subquery = $this->db->get();
			if ($subquery->num_rows() > 0) {
				$laps = $subquery->result();
			};
			// };
			// dd($laps);
		}
		// dd($this->session->userdata());
		// dd($result);
		$this->data['datas'] = $result;
		$this->data['subresult'] = $laps;
		
		$this->data['classes'] = $this->classes_m->get_classes();		
		$this->data['templates'] = $this->certificate_template_m->get_certificate_template();
		$this->data["subview"] = "report/certificate/CertificateReportView";
		$this->load->view('_layout_main', $this->data);
	}

	public function add_laporan()
	{
		$this->data['classes'] = $this->classes_m->get_classes();		
		// $this->data['templates'] = $this->certificate_template_m->get_certificate_template();
		$this->data["subview"] = "report/certificate/create_certificate";
		$this->load->view('_layout_main', $this->data);
	}

	public function update_status($id)
	{
		if ($id){
			$this->db->where('id', $id);
			$this->db->update('laporan_bulanan', ['is_verified' => 1]);
			if ($this->db->affected_rows() > 0) {
				redirect(base_url('certificatereport'));
			} else {
				redirect(base_url('certificatereport'));
			}
		}
	}

	public function download($fileName) {
        $filePath = FCPATH . 'uploads/files/' . $fileName; 

        // Cek jika file ada
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));

            
            flush();
            readfile($filePath);
            exit;
        } else {
            show_404();
        }
    }

	public function cretae_laporan()
	{
		$name = $this->input->post('name'); // Mengambil input dari field 'name'
		$file_name = $_FILES['file']['name'];
		
		$this->form_validation->set_rules('name', 'Name', 'required');
            
		// Validasi untuk file (memastikan bahwa file sudah dipilih)
		if (empty($_FILES['file']['name'])) {
			$this->session->set_flashdata('error', 'Gagal menambahkan data');
			$this->form_validation->set_rules('file', 'File', 'required');
			
		}
		
		// Jika validasi gagal
		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('error', 'Gagal Menambahkan Data');
			redirect(base_url('certificatereport/add_laporan'));
		} else {
			$new_file = '';
                                            
			$path = "./uploads/files/";
			if (!is_dir($path)) {
				mkdir($path, 0755, true); // Create directory with 0755 permissions and recursive flag set to true
			}
			$file_name = $_FILES['file']['name'];
			$random = random19();
			$makeRandom = hash('sha512', $random.$file_name . date('Y-M-d-H:i:s') . config_item("encryption_key"));
			$file_name_rename = $makeRandom;
			$explode = explode('.', (string) $file_name);
			$new_file = $file_name_rename.'.'.end($explode);
			// dd($new_file);
			// die;
			$config['upload_path'] = $path;
			$config['allowed_types'] = 'pdf|xls|xlsx'; // Hanya izinkan PDF dan Excel
        	$config['max_size'] = 2048; // Maksimal 2MB
			$config['file_name'] = $new_file;
			$this->load->library('upload');
			$this->upload->initialize($config);

				// Manually set the file data for this specific file
			// $_FILES['single_file']['name'] = $fileAnswer['name'][$typeID][$questionID];
			// $_FILES['single_file']['type'] = $fileAnswer['type'][$typeID][$questionID];
			// $_FILES['single_file']['tmp_name'] = $fileAnswer['tmp_name'][$typeID][$questionID];
			// $_FILES['single_file']['error'] = $fileAnswer['error'][$typeID][$questionID];
			// $_FILES['single_file']['size'] = $fileAnswer['size'][$typeID][$questionID];

			if(!$this->upload->do_upload("file")) {
				log_message('error', $this->upload->display_errors()); // Log the error message for debugging
				redirect(base_url('certificatereport/add_laporan'));
				// dd(log_message('error', $this->upload->display_errors()));
			}
			// }else{
			// dd($file_name_rename );
			$this->upload->data();
			$this->db->select('*'); // Specify columns you need, or leave '*' to get all
			$this->db->from('student'); // Query the 'online_exam' table
			$this->db->where('studentID', $this->session->userdata('loginuserID')); // Filter by 'parentID'
			
			$secondQuery = $this->db->get();
			$dataresultpertanyaan = [];
			if ($secondQuery->num_rows() > 0) {
				$dataresultpertanyaan = $secondQuery->result(); // Store the result as an array of objects
			}
			$now = new DateTime();
			// dd($dataresultpertanyaan[0]->parentID);
			
			$array = [
				'name' => $name,
				'parent_id' => $dataresultpertanyaan[0]->parentID,
				'file' => $new_file,
				'create_userID' => $this->session->userdata('loginuserID'),
				'create_usertypeID' => $dataresultpertanyaan[0]->usertypeID,
				'original_name' => $file_name,
				'date' => $now->format('Y-m-d')
			];
			// dd($dataresultpertanyaan);
			$this->session->set_flashdata('success', 'Data berhasil ditambahkan');
			$this->laporan_bulanan->insert($array);
			redirect(base_url('certificatereport'));
			
			// } 
			
			// $this->session->set_flashdata('success', 'Sukses');		
			
		}
	}

	public function file_check() {
		$allowed_mime_type_arr = ['application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
		$mime = get_mime_by_extension($_FILES['file']['name']);
	
		if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
			if (in_array($mime, $allowed_mime_type_arr)) {
				return TRUE;
			} else {
				$this->form_validation->set_message('file_check', 'Please select only PDF/Excel files.');
				return FALSE;
			}
		} else {
			$this->form_validation->set_message('file_check', 'Please choose a file to upload.');
			return FALSE;
		}
	}

	protected function rules() 
	{
		return array(
			array(
				'field' => 'classesID',
				'label' => $this->lang->line('certificatereport_classname'),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			),
			array(
				'field' => 'sectionID',
				'label' => $this->lang->line('certificatereport_sectionname'),
				'rules' => 'trim|required|xss_clean'
			),
			array(
				'field' => 'templateID',
				'label' => $this->lang->line('certificatereport_templatename'),
				'rules' => 'trim|required|xss_clean|callback_unique_data'
			)
		);
	}

	public function unique_data($data) {
		if($data === "0") {
			$this->form_validation->set_message('unique_data', 'The %s field is required.');
			return FALSE;
		}
		return TRUE;
	}


	public function getStudentList() {
		$retArray['status'] = FALSE;
		$retArray['render'] = '';
		if(permissionChecker('certificatereport')) {
			$classID 		= $this->input->post('classesID');
			$sectionID 		= $this->input->post('sectionID');
			$templateID 	= $this->input->post('templateID');
			if($_POST !== []) {
				$rules = $this->rules();
				$this->form_validation->set_rules($rules);
				if($this->form_validation->run() == FALSE) {
					$retArray = $this->form_validation->error_array();
					$retArray['status'] = FALSE;
				    echo json_encode($retArray);
				    exit;
				} else {		
					$sections = pluck($this->section_m->get_section(), 'section', 'sectionID');
					$classes = pluck($this->classes_m->get_classes(), 'classes', 'classesID');

					if($sectionID == 0) {
						$students = $this->studentrelation_m->get_studentrelation_join_student(array('srclassesID' => $classID));
						$section = $this->lang->line('certificatereport_select_all_section');
					} else {
						$students = $this->studentrelation_m->get_studentrelation_join_student(array('srclassesID' => $classID, 'srsectionID' => $sectionID));
						$section = $sections[$sectionID];
					}

					$this->data['students']		= $students;
					$this->data['classes'] 		= $classes;
					$this->data['sections'] 	= $sections;
					$this->data['class']		= $classes[$classID];
					$this->data['classesID']	= $classID;
					$this->data['sectionID']	= $sectionID;
					$this->data['section']		= $section;
					$this->data['templateID'] 	= $templateID;
					$retArray['render'] = $this->load->view('report/certificate/CertificateReport', $this->data, true);
					$retArray['status'] = TRUE;
					echo json_encode($retArray);
				    exit;
				}
			}
		} else {
			echo json_encode($retArray);
			exit;
		}
	}

	public function generate_certificate() 
	{


		$this->data['headerassets'] = array(
            'js' => array(
                'assets/CircleType/dist/circletype.min.js'
            )
        );

		$tagArray = array();
		$this->data['themeArray'] = array(
            // '1' => 'default',
            '1' => 'theme1',
            '2' => 'theme2'
        );

		$userID 		= htmlentities((string) escapeString($this->uri->segment(3)));
		$usertypeID 	= htmlentities((string) escapeString($this->uri->segment(4)));
		$templateID 	= htmlentities((string) escapeString($this->uri->segment(5)));
		$classID 		= htmlentities((string) escapeString($this->uri->segment(6)));
		$schoolyearID 	= $this->session->userdata('defaultschoolyearID');


		if((int)$userID && (int)$usertypeID && (int)$templateID && (int)$schoolyearID && (int)$classID) {
			$student = $this->studentrelation_m->get_studentrelation_join_student_with_student_extend(array('srstudentID' => $userID), TRUE);

			$usertype = $this->usertype_m->get_single_usertype(array('usertypeID' => $usertypeID));

			$template = $this->certificate_template_m->get_single_certificate_template(array('certificate_templateID' => $templateID));

			$schoolyear = $this->schoolyear_m->get_single_schoolyear(array('schoolyearID' => $schoolyearID));

			$class = $this->classes_m->get_single_classes(array('classesID' => $classID));




			if(inicompute($student) && inicompute($usertype) && inicompute($template) && inicompute($schoolyear) && inicompute($class)) {



			    $this->data['certificate_template'] = $template;


                $tagClasses = $this->classes_m->get_single_classes(array('classesID' => $student->srclassesID));
                $tagSection = $this->section_m->get_single_section(array('sectionID' => $student->srsectionID));


                $tagGroup = $this->studentgroup_m->get_single_studentgroup(array('studentgroupID' => $student->srstudentgroupID));


                $tagSubject = $this->subject_m->get_single_subject(array('subjectID' => $student->sroptionalsubjectID));


                $country = $this->_country();

                $tagArray['[name]'] 		= $student->name;
				$tagArray['[dob]'] 			= isset($student->dob) ? date("d M Y", strtotime((string) $student->dob)) : '';
				$tagArray['[gender]'] 		= $student->sex;
				$tagArray['[blood_group]'] 	= $student->bloodgroup;
				$tagArray['[religion]'] 	= $student->religion;
				$tagArray['[email]'] 		= $student->email;
				$tagArray['[phone]'] 		= $student->phone;
				$tagArray['[address]']  	= $student->address;
				$tagArray['[state]'] 		= $student->state;
				$tagArray['[country]'] 		= isset($country[$student->country]) ? $country[$student->country] : '';
				$tagArray['[class]'] 		= inicompute($tagClasses) ? $tagClasses->classes : '';
				$tagArray['[section]'] 		= inicompute($tagSection) ? $tagSection->section : '';
				$tagArray['[group]'] 		= inicompute($tagGroup) ? $tagGroup->group : '';
				$tagArray['[optional_subject]'] = inicompute($tagSubject) ? $tagSubject->subject : '';
				$tagArray['[register_no]'] 	= $student->srregisterNO;
				$tagArray['[roll]']	 		= $student->srroll;
				$tagArray['[extra_curricular_activities]'] 	= $student->extracurricularactivities;
				$tagArray['[remarks]'] 		= $student->remarks;
				$tagArray['[username]'] 	= $student->username;
				$tagArray['[date]'] 		= date('d M Y');
				
				
				$this->data['template'] = $this->tagConvertForTemplate($template->template, $tagArray, 3);

				$this->data['top_heading_title'] = $this->tagConvertForTemplate($template->top_heading_title, $tagArray, 3, FALSE);

				$this->data['top_heading_left'] = $this->tagConvertForTemplate($template->top_heading_left, $tagArray, 3, FALSE);

				$this->data['top_heading_middle'] = $this->tagConvertForTemplate($template->top_heading_middle, $tagArray, 3, FALSE);

				$this->data['top_heading_right'] = $this->tagConvertForTemplate($template->top_heading_right, $tagArray, 3, FALSE);

				$this->data['main_middle_text'] = $this->tagConvertForTemplate($template->main_middle_text, $tagArray, 3, FALSE);

				$this->data['footer_left_text'] = $this->tagConvertForTemplate($template->footer_left_text, $tagArray, 3, FALSE);

				$this->data['footer_middle_text'] = $this->tagConvertForTemplate($template->footer_middle_text, $tagArray, 3, FALSE);

				$this->data['footer_right_text'] = $this->tagConvertForTemplate($template->footer_right_text, $tagArray, 3, FALSE);

				$this->data['theme'] = $this->data['themeArray'][$template->theme];

				$this->load->view('report/certificate/CertificateReportLayout', $this->data);		
			} else {
				$this->data["subview"] = "error";
				$this->load->view('_layout_main', $this->data);
			}
		} else {
			$this->data["subview"] = "error";
			$this->load->view('_layout_main', $this->data);
		}
	}


	private function tagConvertForTemplate($message, $convertArray, $usertypeID=1, $design = TRUE) {
        if ($message && $usertypeID == 3) {
            $userTags = pluck($this->mailandsmstemplatetag_m->get_order_by_mailandsmstemplatetag(array('usertypeID' => 3)), 'tagname');
            if(inicompute($userTags)) {
	                foreach ($userTags as $key => $userTag) {
	                    if(array_key_exists($userTag, $convertArray)) {
	                        $length = strlen((string) $convertArray[$userTag]);
	                        $width = (20*$length);
	                        if($design) {
	                        	$message = str_replace($userTag, '<span style="width:'.$width.'px;" class="dots widthcss" data-hover="'.$convertArray[$userTag].'"></span>' , (string) $message);
	                        } else {
	                        	$message = str_replace($userTag, $convertArray[$userTag], (string) $message);
	                        }

	                    }
	                }
	            }
        }
        return $message;
    }

	public function getSection()
	{
		$id = $this->input->post('id');
		if((int)$id !== 0) {
			$allSection = $this->section_m->get_order_by_section(array('classesID' => $id));
			echo "<option value='0'>", $this->lang->line("certificatereport_please_select"),"</option>";
			foreach ($allSection as $value) {
				echo "<option value=\"$value->sectionID\">",$value->section,"</option>";
			}

		}
	}
}
