<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Signin extends Admin_Controller {
public $load;
 public $session;
 public $data;
 public $lang;
 public $signin_m;
 public $form_validation;
 public $input;
 public $recaptcha;
 public $usertype_m;
 public $user_m;
 public $loginlog_m;
 public $updatechecker;
 public $db;
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
        $this->load->model("signin_m");
        $this->load->model("user_m");
        $this->load->model("usertype_m");
        $this->load->model("loginlog_m");
        $this->load->helper('cookie');
        $this->load->library('updatechecker');

        $this->session->set_userdata($this->data["siteinfos"]->language);
        $language = $this->session->userdata('lang');
        $this->lang->load('signin', $language);
        if ( !isset($this->data["siteinfos"]->captcha_status) ) {
            $this->data["siteinfos"]->captcha_status = 1;
        }

	}

	public function index() {
     
        if ( $this->data['siteinfos']->captcha_status == 0 ) {
            $this->load->library('recaptcha');
            $this->data['recaptcha'] = [
                'widget' => $this->recaptcha->getWidget(),
                'script' => $this->recaptcha->getScriptTag(),
            ];
        }

        if ($this->signin_m->loggedin() != FALSE) {
            redirect(base_url('dashboard/index'));
        }
        $this->data['form_validation'] = 'No';
        if($_POST !== []) {
            $this->_setCookie();
            $rules = $this->rules();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == FALSE) {
                $this->data['form_validation'] = validation_errors();
                $this->data["subview"]         = "signin/index";
            	$this->load->view('_layout_signin', $this->data);
            } else {
                $signinManager = $this->_signInManager();
                if($signinManager['return']) {
                    redirect(base_url('dashboard/index'));
                } else {
                    $this->data['form_validation'] = $signinManager['message'];
                    $this->data["subview"]         = "signin/index";
                    $this->load->view('_layout_signin', $this->data);
                }
            }
        } else {
            $this->data["subview"]         = "signin/index";
            $this->load->view('_layout_signin', $this->data);
            $this->session->sess_destroy();
        }
	}

	public function cpassword() {
		$this->load->library("session");
		if($_POST !== []) {
			$rules = $this->rules_cpassword();
			$this->form_validation->set_rules($rules);
			if ($this->form_validation->run() == FALSE) {
				$this->data["subview"] = "signin/cpassword";
				$this->load->view('_layout_main', $this->data);
			} else {
				$this->session->set_flashdata('success', $this->lang->line('menu_success'));
				redirect(base_url('signin/cpassword'));
			}
		} else {
			$this->data["subview"] = "signin/cpassword";
			$this->load->view('_layout_main', $this->data);
		}
	}

	public function signout() {
		$this->signin_m->signout();
		redirect(base_url("signin/index"));
	}

    protected function rules()
    {
        $rules = [
            [
                'field' => 'username',
                'label' => "Username",
                'rules' => 'trim|required|max_length[40]|xss_clean'
            ],
            [
                'field' => 'password',
                'label' => "Password",
                'rules' => 'trim|required|max_length[40]|xss_clean'
            ]
        ];
        if ( $this->data["siteinfos"]->captcha_status == 0 ) {
            $rules[] = [
                'field' => 'g-recaptcha-response',
                'label' => "captcha",
                'rules' => 'trim|required'
            ];
        }
        return $rules;
    }

    protected function rules_cpassword()
    {
        return [
            [
                'field' => 'old_password',
                'label' => $this->lang->line('old_password'),
                'rules' => 'trim|required|max_length[40]|min_length[4]|xss_clean|callback_old_password_unique'
            ],
            [
                'field' => 'new_password',
                'label' => $this->lang->line('new_password'),
                'rules' => 'trim|required|max_length[40]|min_length[4]|xss_clean'
            ],
            [
                'field' => 're_password',
                'label' => $this->lang->line('re_password'),
                'rules' => 'trim|required|max_length[40]|min_length[4]|matches[new_password]|xss_clean'
            ]
        ];
    }

    public function old_password_unique() {
		$changePassword  = $this->_changePassword();
		if(!$changePassword) {
			$this->form_validation->set_message("old_password_unique", "The %s does not match");
			return FALSE;
		}
		return TRUE;
	}

	private function _setCookie()
    {
        if ( isset($_POST['remember']) ) {
            set_cookie('remember_username', $this->input->post('username'), time() + ( 86400 * 30 ));
            set_cookie('remember_password', $this->input->post('password'), time() + ( 86400 * 30 ));
        } else {
            delete_cookie('remember_username');
            delete_cookie('remember_password');
        }
    }

    private function _signInManager()
    {

        $verifyValidUser     = true;
        $returnArray         = [
            'return'  => true,
            'message' => 'Success'
        ];
        $setting             = $this->data['siteinfos'];
        $lang                = $setting->language;
        $defaultSchoolYearID = $setting->school_year;
        $username            = $this->input->post('username');
        $password            = $this->input->post('password');
        $user                = $this->_userChecker($username, $password);
        $userID              = inicompute($user) ? $user->userID : 0;
        $user                = inicompute($user) ? $user->info : [];


        if ( isset($setting->captcha_status) && $setting->captcha_status == 0 ) {
            $captchaResponse = $this->recaptcha->verifyResponse($this->input->post('g-recaptcha-response'));
        } else {
            $captchaResponse = [ 'success' => true ];
        }

        if ( $returnArray['return'] ) {
            if ( $captchaResponse['success'] ) {
                if ( inicompute($user) ) {
                    $userType = $this->usertype_m->get_single_usertype([ 'usertypeID' => $user->usertypeID ]);
                    if ( inicompute($userType) ) {
                        if ( $user->active ) {
                            $this->_loginLog($user->usertypeID, $userID);
                            $session = [
                                "loginuserID"         => $userID,
                                "name"                => $user->name,
                                "email"               => $user->email,
                                "usertypeID"          => $user->usertypeID,
                                'usertype'            => $userType->usertype,
                                "username"            => $user->username,
                                "photo"               => $user->photo,
                                "lang"                => $lang,
                                "defaultschoolyearID" => $defaultSchoolYearID,
                                "varifyvaliduser"     => $verifyValidUser,
                                "loggedin"            => true
                            ];

                            $this->session->set_userdata($session);
                            $returnArray = [ 'return' => true, 'message' => 'Success' ];
                        } else {
                            $returnArray = [ 'return' => false, 'message' => 'You are blocked.' ];
                        }
                    } else {
                        $returnArray = [ 'return' => false, 'message' => 'This user role does not exist.' ];
                    }
                } else {
                    $returnArray = [ 'return' => false, 'message' => 'Incorrect Signin.' ];
                }
            } else {
                $captchaResponseError = ( is_array($captchaResponse['error-codes']) ) ? $captchaResponse['error-codes'][0] : $captchaResponse['error-codes'];
                $returnArray          = [ 'return' => false, 'message' => $captchaResponseError ];
            }
        } else {
            $returnArray = [ 'return' => false, 'message' => $returnArray['message'] ];
        }

        return $returnArray;
    }

    private function _userChecker( $username, $password )
    {
        $tables   = [
            'student'     => 'student',
            'parents'     => 'parents',
            'teacher'     => 'teacher',
            'user'        => 'user',
            'systemadmin' => 'systemadmin'
        ];
        $userInfo = [ 'info' => [], 'userID' => 0, 'idName' => '' ];
        foreach ( $tables as $table ) {
            $user = $this->user_m->get_user_table($table, $username, $password);
            if ( inicompute($user) ) {
                $id                 = $table . 'ID';
                $userInfo['info']   = $user;
                $userInfo['userID'] = $user->$id;
                $userInfo['idName'] = $table . 'ID';
            }
        }
        return (object) $userInfo;
    }

    private function _loginLog( $userTypeID, $userID )
    {
        $getPreviousData = $this->loginlog_m->get_single_loginlog([
            'userID'     => $userID,
            'usertypeID' => $userTypeID,
            'ip'         => $this->updatechecker->getUserIP(),
            'browser'    => $this->updatechecker->getBrowser()->name,
            'logout'     => null
        ]);

        if ( inicompute($getPreviousData) ) {
            $this->loginlog_m->update_loginlog(['logout' => ( $getPreviousData->login + ( 60 * 5 ) )], $getPreviousData->loginlogID);
        }

        $this->loginlog_m->insert_loginlog([
            'ip'              => $this->updatechecker->getUserIP(),
            'browser'         => $this->updatechecker->getBrowser()->name,
            'operatingsystem' => $this->updatechecker->getBrowser()->platform,
            'login'           => strtotime(date('YmdHis')),
            'usertypeID'      => $userTypeID,
            'userID'          => $userID,
        ]);
    }

    private function _updateCodeChecker()
    {
        return $this->updatechecker->verifyValidUser();
    }

    private function _changePassword() {
    	$tables = [
            'student'     => 'student',
            'parents'     => 'parents',
            'teacher'     => 'teacher',
            'user'        => 'user',
            'systemadmin' => 'systemadmin'
        ];

        $username      = $this->session->userdata("username");
        $old_password  = $this->signin_m->hash($this->input->post('old_password'));
        $new_password  = $this->signin_m->hash($this->input->post('new_password'));
        
        $db_old_password   = '';
        $update_table_name = '';
        foreach ( $tables as $table ) {
            $user  = $this->db->get_where($table, [ "username" => $username, "password" => $old_password ])->row();
            if (inicompute($user)) {
                $db_old_password   = $user->password;
                $update_table_name = $table;
                break;
            }
        }

        if(isset($db_old_password) && ( $db_old_password == $old_password)) {
            $array = [
                "password" => $new_password
            ];
            $this->db->where([ "username" => $username, "password" => $old_password ]);
            $this->db->update($update_table_name, $array);
            return true;
        }
        return false;
    }

}



