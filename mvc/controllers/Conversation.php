<?php defined('BASEPATH') || exit('No direct script access allowed');

class Conversation extends Admin_Controller {
	public $load;
 public $session;
 public $lang;
 public $conversation_m;
 public $data;
 public $form_validation;
 public $input;
 public $upload_data;
 public $uri;
 public $alert_m;
 public $user_m;
 public $systemadmin_m;
 public $teacher_m;
 public $student_m;
 public $parents_m;
 public $classes_m;
 public $upload;
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
        $this->load->model('usertype_m');
        $this->load->model('classes_m');
        $this->load->model('user_m');
        $this->load->model('teacher_m');
        $this->load->model('parents_m');
        $this->load->model('systemadmin_m');
        $this->load->model('conversation_m');
        $this->load->model('student_m');
        
        $language = $this->session->userdata('lang');
        $this->lang->load('conversation', $language);
	}

	public function index()
    {
        $conversations = $this->conversation_m->get_my_conversations();
        $this->data['conversations'] = $this->_conversation($conversations);
        $this->data["subview"] = "conversation/index";
        $this->load->view('_layout_main', $this->data);
    }

	public function draft()
    {
        $conversations = $this->conversation_m->get_my_conversations_draft();
        $this->data['conversations'] = $this->_conversation($conversations, false);
        $this->data["subview"] = "conversation/draft";
        $this->load->view('_layout_main', $this->data);
    }

    public function sent()
    {
        $conversations = $this->conversation_m->get_my_conversations_sent();
        $this->data['conversations'] = $this->_conversation($conversations);
        $this->data["subview"] = "conversation/index";
        $this->load->view('_layout_main', $this->data);
    }

    public function trash()
    {
        $conversations = $this->conversation_m->get_my_conversations_trash();
        $this->data['conversations'] = $this->_conversation($conversations);
        $this->data["subview"] = "conversation/index";
        $this->load->view('_layout_main', $this->data);
    }

    public function draft_send( $id )
    {
        if ( (int) $id !== 0 ) {
            $conversation = $this->conversation_m->get_conversation($id);
            if ( inicompute($conversation) && $conversation->draft == 1 ) {
                $this->conversation_m->update_conversation(['draft' => 0], $id);
                $this->session->set_flashdata('success', $this->lang->line("menu_success"));
            } else {
                $this->session->set_flashdata('error', 'Draft message not found.');
            }
        }
        redirect(base_url("conversation/index"));
    }

	public function create()
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
        $userTypeID                 = $this->session->userdata("usertypeID");
        $userID                     = $this->session->userdata("loginuserID");
        $this->data['usertypes']    = $this->conversation_m->get_usertype_by_permission();
        $this->data['classes']      = $this->classes_m->get_classes();
        if ( $_POST !== [] ) {
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data['form_validation'] = validation_errors();
                $this->data['GroupID']         = $this->input->post('userGroup');
                $this->data["subview"]         = "conversation/add_group";
                $this->load->view('_layout_main', $this->data);
            } elseif ($this->input->post('userGroup')) {
                $conversation = [
                    'create_date' => date("Y-m-d H:i:s"),
                    'modify_date' => date("Y-m-d H:i:s"),
                    'draft'       => ( ( $this->input->post('submit') == "draft" ) ? 1 : 0 )
                ];
                $conversationID = $this->conversation_m->insert_conversation($conversation);
                $conversationUser = [
                    'conversation_id' => $conversationID,
                    'user_id'         => $userID,
                    'usertypeID'      => $userTypeID,
                    'is_sender'       => 1,
                ];
                $this->conversation_m->insert_conversation_user($conversationUser);
                $conversationMessage = [
                    'user_id'          => $userID,
                    'usertypeID'       => $userTypeID,
                    'subject'          => $this->input->post('subject'),
                    'msg'              => $this->input->post('message'),
                    'create_date'      => date("Y-m-d H:i:s"),
                    'modify_date'      => date("Y-m-d H:i:s"),
                    'start'            => 1,
                    'attach'           => $this->upload_data['file']['attach'],
                    'attach_file_name' => $this->upload_data['file']['attach_file_name']
                ];
                $userGroup = $this->input->post('userGroup');
                $users     = $this->_currentUser($userGroup);
                $this->_messageCreate($userGroup, $users, $conversationID, $conversationMessage);
                $this->session->set_flashdata('success', $this->lang->line("success_msg"));
                redirect(base_url('conversation/index'));
            }
        } else {
            $this->data['GroupID'] = 0;
            $this->data["subview"] = "conversation/add_group";
            $this->load->view('_layout_main', $this->data);
        }
    }

	public function view()
    {
        $userTypeID     = $this->session->userdata("usertypeID");
        $userID         = $this->session->userdata("loginuserID");
        $conversationID = htmlentities((string) escapeString($this->uri->segment(3)));
        if ( (int) $conversationID !== 0 ) {
            $conversationUser = $this->conversation_m->user_check($conversationID, $userID, $userTypeID);
            if ( inicompute($conversationUser) && $conversationUser->trash != 2 ) {
                $conversations          = $this->conversation_m->get_conversation_msg_by_id($conversationID);
                $this->data['messages'] = $this->_conversation($conversations, false, [ 'photo' => 'photo' ]);
                $this->_alertPost($conversationID);
                if ( $_POST !== [] ) {
                    $rules = $this->rules(true);
                    $this->form_validation->set_rules($rules);
                    if ( $this->form_validation->run() == false ) {
                        $this->session->set_flashdata('error', trim(strip_tags((string) validation_errors())));
                    } else {
                        $conversationMessage = [
                            'conversation_id'  => $conversationID,
                            'msg'              => $this->input->post('reply'),
                            'user_id'          => $userID,
                            'usertypeID'       => $userTypeID,
                            'create_date'      => date("Y-m-d H:i:s"),
                            'modify_date'      => date("Y-m-d H:i:s"),
                            'attach'           => $this->upload_data['file']['attach'],
                            'attach_file_name' => $this->upload_data['file']['attach_file_name']
                        ];

                        $messageID = $this->conversation_m->insert_conversation_msg($conversationMessage);
                        if ( $messageID > 0 ) {
                            $this->alert_m->insert_alert([
                                'itemID'     => $messageID,
                                "userID"     => $this->session->userdata("loginuserID"),
                                'usertypeID' => $this->session->userdata('usertypeID'),
                                'itemname'   => 'message'
                            ]);
                        }
                    }
                    redirect(base_url("conversation/view/$conversationID"));
                }
                $this->data["subview"] = "conversation/view";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function delete_conversation()
    {
        $id = htmlentities((string) escapeString($this->input->post('id')));
        if ( $id !== '' && $id !== '0' ) {
            $array        = explode(',', $id);
            foreach ( $array as $value ) {
            	if((int)$value !== 0) {
                	$this->conversation_m->trash_conversation(['trash' => 1], $value);
            	}
            }
            $this->session->set_flashdata('success', $this->lang->line("deleted"));
        } else {
            $this->session->set_flashdata('error', $this->lang->line("delete_error"));
        }
    }

    public function delete_trash_to_trash()
    {
        $id = htmlentities((string) escapeString($this->input->post('id')));
        if ( $id !== '' && $id !== '0' ) {
            $array        = explode(',', $id);
            foreach ( $array as $value ) {
            	if((int)$value !== 0) {
                	$this->conversation_m->trash_conversation(['trash' => 2], $value);
                }
            }
            $this->session->set_flashdata('success', $this->lang->line("deleted"));
        } else {
            $this->session->set_flashdata('error', $this->lang->line("delete_error"));
        }
    }

    public function open()
    {
        $conversationID = htmlentities((string) escapeString($this->uri->segment(3)));
        $messageID      = htmlentities((string) escapeString($this->uri->segment(4)));

        if ( (int) $conversationID && (int) $messageID ) {
            $conversationUser = $this->conversation_m->user_check($conversationID,
                $this->session->userdata('loginuserID'), $this->session->userdata('usertypeID'));
            if ( inicompute($conversationUser) && $conversationUser->trash != 2) {
                $conversation = $this->conversation_m->get_single_conversation_msg([ 'msg_id' => $messageID ]);
                if ( inicompute($conversation) ) {
                    $file = realpath('uploads/attach/' . $conversation->attach_file_name);
                    if ( file_exists($file) ) {
                        $expFileName  = explode('.', $file);
                        $originalname = ( $conversation->attach ) . '.' . end($expFileName);
                        header('Content-Description: File Transfer');
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . basename($originalname) . '"');
                        header('Expires: 0');
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . filesize($file));
                        readfile($file);
                        exit;
                    } else {
                        redirect(base_url('conversation/index/' . $conversationID));
                    }
                } else {
                    redirect(base_url('conversation/index/' . $conversationID));
                }
            } else {
                redirect(base_url('conversation/index/' . $conversationID));
            }
        } else {
            redirect(base_url('conversation/index'));
        }
    }

	private function _conversation($conversations = [], $message = true, $binds = [])
    {
        $this->data['methodpass']    = htmlentities((string) escapeString($this->uri->segment(2)));
        if ( inicompute($conversations) ) {
            foreach ( $conversations as $conversationKey => $conversation ) {
                $user = $this->user_m->get_user_info($conversation->usertypeID, $conversation->user_id);
                if(inicompute($user)) {
                    $pushItem = ['sender' => $user->name];
                    if($message) {
                        $messages = $this->conversation_m->get_conversation_msg_by_id($conversation->conversation_id);
                        $pushItem['msgCount'] = inicompute($messages);
                    }

                    if(inicompute($binds)) {
                        foreach ($binds as $bindKey => $bind) {
                            $pushItem[$bindKey] = $user->$bind;
                        }
                    }
                    $conversations[ $conversationKey ] = (object) array_merge((array) $conversation, $pushItem);
                }
            }
        }
        return $conversations;
    }

    private function _currentUser( $userGroupID )
    {
        if ( $userGroupID == 1 ) {
            if ( !$this->input->post('systemadminID') ) {
                $users = $this->systemadmin_m->get_systemadmin();
            } else {
                $systemadminID = $this->input->post('systemadminID');
                $users         = $this->systemadmin_m->get_order_by_systemadmin([ 'systemadminID' => $systemadminID ]);
            }
        } elseif ( $userGroupID == 2 ) {
            if ( !$this->input->post('teacherID') ) {
                $users = $this->teacher_m->get_teacher();
            } else {
                $teacherID = $this->input->post('teacherID');
                $users     = $this->teacher_m->get_order_by_teacher([ 'teacherID' => $teacherID ]);
            }
        } elseif ( $userGroupID == 3 ) {
            if ( !$this->input->post('classID') ) {
                $users = $this->student_m->get_order_by_student();
            } else {
                $classID   = $this->input->post('classID');
                $studentID = $this->input->post('studentID');
                if ( $studentID > 0 ) {
                    $users = $this->student_m->get_order_by_student(['studentID'    => $studentID]);
                } else {
                    $users = $this->student_m->get_order_by_student(['classesID'    => $classID]);
                }
            }
        } elseif ( $userGroupID == 4 ) {
            if ( !$this->input->post('parentID') ) {
                $users = $this->parents_m->get_parents();
            } else {
                $parentsID = $this->input->post('parentID');
                $users     = $this->parents_m->get_order_by_parents([ 'parentsID' => $parentsID ]);
            }
        } elseif (!$this->input->post('userID')) {
            $users = $this->user_m->get_order_by_user([ 'usertypeID' => $userGroupID ]);
        } else {
            $userID = $this->input->post('userID');
            $users  = $this->user_m->get_order_by_user([ 'userID' => $userID ]);
        }
        return $users;
    }

    private function _messageCreate( $userTypeID, $users = [], $conversationID = 0, $message = [] )
    {
        if ( inicompute($users) ) {
            $userType = [
                1 => 'systemadminID',
                2 => 'teacherID',
                3 => 'studentID',
                4 => 'parentsID',
                5 => 'userID',
            ];

            $conversationTeacher = [];
            foreach ( $users as $user ) {
                $userID                = ( isset($userType[ $userTypeID ]) ? $userType[ $userTypeID ] : $userType[5] );
                $conversationTeacher[] = [
                    'conversation_id' => $conversationID,
                    "user_id"         => $user->$userID,
                    "usertypeID"      => $user->usertypeID,
                    'is_sender'       => 0
                ];
            }

            $message['conversation_id'] = $conversationID;
            $this->conversation_m->batch_insert_conversation_user($conversationTeacher);
            $messageID = $this->conversation_m->insert_conversation_msg($message);
            
            if ( $messageID > 0 ) {
                $this->alert_m->insert_alert([
                    'itemID'     => $messageID,
                    "userID"     => $this->session->userdata("loginuserID"),
                    'usertypeID' => $this->session->userdata('usertypeID'),
                    'itemname'   => 'message'
                ]);
            }
        }
    }

	public function classCall() {
		$allsection = $this->classes_m->get_classes();
		echo "<option value='0'>", $this->lang->line("select_class"),"</option>";
		foreach ($allsection as $value) {
			echo "<option value=\"$value->classesID\">",$value->classes,"</option>";
		}
	}

	public function adminCall() {
		$alladmin = $this->systemadmin_m->get_systemadmin();
		echo "<option value='0'>", $this->lang->line("select_admin"),"</option>";
		foreach ($alladmin as $value) {
			echo "<option value=\"$value->systemadminID\">",$value->name,"</option>";
		}
	}

	public function teacherCall() {
		$allteacher = $this->teacher_m->get_teacher();
		echo "<option value='0'>", $this->lang->line("select_teacher"),"</option>";
		foreach ($allteacher as $value) {
			echo "<option value=\"$value->teacherID\">",$value->name,"</option>";
		}
	}

	public function parentCall() {
		$allteacher = $this->parents_m->get_parents();
		echo "<option value='0'>", $this->lang->line("select_parent"),"</option>";
		foreach ($allteacher as $value) {
			echo "<option value=\"$value->parentsID\">",$value->name,"</option>";
		}
	}

	public function userCall() {
		$id = $this->input->post('id');
		$allteacher = $this->user_m->get_order_by_user(array('usertypeID' => $id));
		echo "<option value='0'>", $this->lang->line("select_user"),"</option>";
		foreach ($allteacher as $value) {
			echo "<option value=\"$value->userID\">",$value->name,"</option>";
		}
	}

	public function call_all_student() {
		$classesID = $this->input->post('id');
		if((int)$classesID !== 0) {
			echo "<option value='". 0 ."'>". $this->lang->line('select_student') ."</option>";
			$students = $this->student_m->get_order_by_student(array('classesID' => $classesID));
			foreach ($students as $key => $student) {
				echo "<option value='". $student->studentID ."'>". $student->name ."</option>";
			}
		} else {
			echo "<option value='". 0 ."'>". $this->lang->line('invoice_select_student') ."</option>";
		}
	}

	public function fav_status() {
		$id = $this->input->post('id');
		if ((int)$id !== 0) {
			$data = array();
			$conversation = $this->conversation_m->get_conversation($id);
			$data['fav_status'] = $conversation->fav_status==1 ? 0 : 1;
			$this->conversation_m->update_conversation($data, $id);
			$string = base_url("conversation/index");
			echo $string;
		}
	}

	private function _alertPost( $conversationID = 0 )
    {
        $pluckMessage = pluck($this->alert_m->get_order_by_alert([
            "userID"     => $this->session->userdata("loginuserID"),
            'usertypeID' => $this->session->userdata('usertypeID'),
            'itemname'   => 'message'
        ]), 'itemname', 'itemID');

        $messages = $this->conversation_m->get_conversation_msg_by_id($conversationID);
        if ( inicompute($messages) ) {
            foreach ( $messages as $message ) {
                if ( !isset($pluckMessage[ $message->msg_id ]) ) {
                    $this->alert_m->insert_alert([
                        'itemID'     => $message->msg_id,
                        "userID"     => $this->session->userdata("loginuserID"),
                        'usertypeID' => $this->session->userdata('usertypeID'),
                        'itemname'   => 'message'
                    ]);
                }
            }
        }
    }

	protected function rules($reply = false)
    {
        $rules = [
            [
                'field' => 'userGroup',
                'label' => $this->lang->line("select_group"),
                'rules' => 'trim|required|xss_clean|max_length[11]|numeric|callback_unique_data'
            ],
            [
                'field' => 'message',
                'label' => $this->lang->line("message"),
                'rules' => 'trim|xss_clean|max_length[500]|callback_unique_message'
            ],
            [
                'field' => 'subject',
                'label' => $this->lang->line("subject"),
                'rules' => 'trim|required|xss_clean|max_length[250]'
            ],
            [
                'field' => 'attachment',
                'label' => $this->lang->line("attachment"),
                'rules' => 'trim|xss_clean|max_length[500]|callback_fileUpload'
            ]
        ];

        if($reply) {
            unset($rules[0], $rules[1], $rules[2]);
            $rules[] = [
                'field' => 'reply',
                'label' => 'message',
                'rules' => 'trim|xss_clean|max_length[500]|callback_unique_message'
            ];
        }
        return $rules;
    }

	public function unique_data() {
		if($this->input->post('userGroup') == 0) {
			$this->form_validation->set_message("unique_data", "The %s field is required");
	     	return FALSE;
		}
		return TRUE;
	}

	public function unique_message($message)
    {
        if ( $message == '' && $_FILES["attachment"]['name'] == "") {
            $this->form_validation->set_message("unique_message", "The %s field is required");
            return false;
        }
        return true;
    }

    public function fileUpload()
    {
        if ( $_FILES["attachment"]['name'] != "" ) {
            $file_name        = $_FILES["attachment"]['name'];
            $random           = random19();
            $makeRandom       = hash('sha512',
                $random . $this->session->userdata('username') . config_item("encryption_key"));
            $file_name_rename = $makeRandom;
            $explode          = explode('.', (string) $file_name);
            if ( inicompute($explode) >= 2 ) {
                if ( preg_match('/\s/', (string) $file_name) ) {
                    $file_name = str_replace(' ', '_', (string) $file_name);
                }
                $new_file                = $file_name_rename . '.' . end($explode);
                $config['upload_path']   = "./uploads/attach";
                $config['allowed_types'] = "gif|jpg|png|pdf|doc|csv|docx|xlsx|xl";
                $config['file_name']     = $new_file;
                $config['max_size']      = '1024';
                $config['max_width']     = '3000';
                $config['max_height']    = '3000';
                $this->load->library('upload', $config);
                if ( !$this->upload->do_upload("attachment") ) {
                    $this->form_validation->set_message("fileUpload", $this->upload->display_errors());
                    return false;
                } else {
                    $this->upload_data['file'] = [ 'attach' => $file_name, 'attach_file_name' => $new_file ];
                    return true;
                }
            } else {
                $this->form_validation->set_message("fileUpload", "Invalid file");
                return false;
            }
        } else {
            $this->upload_data['file'] = [ 'attach' => NULL, 'attach_file_name' => NULL ];
            return true;
        }
    }
}

