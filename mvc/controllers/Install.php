<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Install extends CI_Controller
{
    public $load;
    public $uri;
    public $config;
    public $form_validation;
    public $input;
    public $install_m;
    public $db;
    public $systemadmin_m;
    public $update_m;
    public $session;
    public $updatechecker;
    public $dbutil;
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

    protected $_info;
    protected $_internet_connection = false;
    protected $data                 = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->library('updatechecker');
        $this->load->helper('url');
        $this->load->helper('html');
        $this->load->helper('form');
        $this->load->helper('file');
        $this->load->config('iniconfig');

        if ($this->_checkInternetConnection()) {
            $this->_internet_connection = true;
        }

        $uri = strpos((string) $this->uri->uri_string(), 'install');
        if ($uri == false && $this->config->config_install()) {
            redirect(site_url('signin/index'));
        }
    }

    public function index()
    {
        $this->data['errors']  = [];
        $this->data['success'] = [];

        // Check PHP version
        if (phpversion() < "5.6") {
            $this->data['errors'][] = 'You are running PHP old version!';
        } else {
            $phpversion              = phpversion();
            $this->data['success'][] = ' You are running PHP ' . $phpversion;
        }

        // Check Mysql PHP exention
        if (!extension_loaded('mysqli')) {
            $this->data['errors'][] = 'Mysqli PHP extension unloaded!';
        } else {
            $this->data['success'][] = 'Mysqli PHP extension loaded!';
        }
        // Check MBString PHP exention
        if (!extension_loaded('mbstring')) {
            $this->data['errors'][] = 'MBString PHP extension unloaded!';
        } else {
            $this->data['success'][] = 'MBString PHP extension loaded!';
        }
        // Check GD PHP exention
        if (!extension_loaded('gd')) {
            $this->data['errors'][] = 'GD PHP extension unloaded!';
        } else {
            $this->data['success'][] = 'GD PHP extension loaded!';
        }
        // Check CURL PHP exention
        if (!extension_loaded('curl')) {
            $this->data['errors'][] = 'CURL PHP extension unloaded!';
        } else {
            $this->data['success'][] = 'CURL PHP extension loaded!';
        }
        // Check Zip PHP exention
        if (version_compare(phpversion(), '7.3', '<')) {
            if (!extension_loaded('zip')) {
                $this->data['errors'][] = 'Zip PHP extension unloaded!';
            } else {
                $this->data['success'][] = 'Zip PHP extension loaded!';
            }
        }
        // Check Config Path
        if (@include($this->config->config_path)) {
            $this->data['success'][] = 'Config file is loaded';
            @chmod($this->config->config_path, FILE_WRITE_MODE);
            if (is_really_writable($this->config->config_path) == true) {
                $this->data['success'][] = 'Config file is writable!';
            } else {
                $this->data['errors'][] = 'Config file is non-writable!';
            }
        } else {
            $this->data['errors'][] = 'Config file is unloaded!';
        }
        // Check Database Path
        if (@include($this->config->database_path)) {
            $this->data['success'][] = 'Database file is loaded!';
            @chmod($this->config->database_path, FILE_WRITE_MODE);
            if (is_really_writable($this->config->database_path) === false) {
                $this->data['errors'][] = 'database file is non-writable!';
            } else {
                $this->data['success'][] = 'Database file is writable!';
            }
        } else {
            $this->data['errors'][] = 'Database file is unloaded!';
        }
        //Check Purchase Path
        if (file_exists($this->config->purchase_path)) {
            $this->data['success'][] = 'Purchase file is loaded';
            @chmod($this->config->purchase_path, FILE_WRITE_MODE);
            if (is_really_writable($this->config->purchase_path) === false) {
                $this->data['errors'][] = 'Purchase file is non-writable!';
            } else {
                $this->data['success'][] = 'Purchase file is writable!';
            }
        } else {
            $this->data['errors'][] = 'Purchase file is unloaded!';
        }

        if ($this->_internet_connection) {
            $this->data['success'][] = 'Internet connection OK!';
        } else {
            $this->data['errors'][] = 'Internet connection problem!';
        }

        // Check allow_url_fopen
        if (ini_get('allow_url_fopen')) {
            $this->data['success'][] = 'allow_url_fopen is enable.';
        } else {
            $this->data['errors'][] = 'allow_url_fopen is disable. enable it to your php.ini file.';
        }

        $this->data["subview"] = "install/index";
        $this->load->view('_layout_install', $this->data);
    }

    public function purchasecode()
    {
        if ($_POST !== []) {
            $rules = $this->rules_purchasecode();
            $this->form_validation->set_rules($rules);
            if ($this->form_validation->run() == false) {
                $this->data["subview"] = "install/purchase_code";
                $this->load->view('_layout_install', $this->data);
            } else {
                $file = APPPATH . 'config/purchase.php';
                $uac  = json_encode([
                    trim((string) $this->input->post('purchase_username')),
                    trim((string) $this->input->post('purchase_code')),
                ]);
                @chmod($file, FILE_WRITE_MODE);
                write_file($file, $uac);
                redirect(base_url("install/database"));
            }
        } else {
            $this->data["subview"] = "install/purchase_code";
            $this->load->view('_layout_install', $this->data);
        }
    }

    public function database()
    {
        $purchaseCodeChecker = $this->_purchaseCodeChecker();
        if (isset($purchaseCodeChecker->status) && $purchaseCodeChecker->status) {
            if ($_POST !== []) {
                $rules = $this->rules_database();
                $this->form_validation->set_rules($rules);
                if ($this->form_validation->run() == false) {
                    $this->data["subview"] = "install/database";
                    $this->load->view('_layout_install', $this->data);
                } else {
                    redirect(site_url("install/timezone"));
                }
            } else {
                $this->data["subview"] = "install/database";
                $this->load->view('_layout_install', $this->data);
            }
        } else {
            redirect(site_url("install/purchasecode"));
        }
    }

    public function timezone()
    {
        $purchaseCodeChecker = $this->_purchaseCodeChecker();
        if (isset($purchaseCodeChecker->status) && $purchaseCodeChecker->status) {
            if ($this->_checkDatabaseConnection()) {
                if ($_POST !== []) {
                    $rules = $this->rules_timezone();
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == false) {
                        $this->data["subview"] = "install/timezone";
                        $this->load->view('_layout_install', $this->data);
                    } else {
                        $array = [
                            'time_zone' => $this->input->post('timezone'),
                        ];

                        $this->load->model('install_m');
                        $this->install_m->insertorupdate($array);
                        redirect(site_url("install/site"));
                    }
                } else {
                    $this->data["subview"] = "install/timezone";
                    $this->load->view('_layout_install', $this->data);
                }
            } else {
                redirect(site_url("install/database"));
            }
        } else {
            redirect(site_url("install/purchasecode"));
        }
    }

    public function site()
    {
        $purchaseCodeChecker = $this->_purchaseCodeChecker();
        if (isset($purchaseCodeChecker->status) && $purchaseCodeChecker->status) {
            if ($this->_checkDatabaseConnection()) {
                if ($_POST !== []) {
                    $this->load->library('session');
                    unset($this->db);
                    $rules = $this->rules_site();
                    $this->form_validation->set_rules($rules);
                    if ($this->form_validation->run() == false) {
                        $this->data["subview"] = "install/site";
                        $this->load->view('_layout_install', $this->data);
                    } else {
                        $this->load->helper('form');
                        $this->load->helper('url');
                        $this->load->model('install_m');
                        $this->load->model('systemadmin_m');
                        $this->load->model('update_m');
                        $purchaseFileRead = $this->_purchaseFileRead();

                        $array = [
                            'address'              => $this->input->post("address"),
                            'backend_theme'        => 'default',
                            'currency_code'        => $this->input->post("currency_code"),
                            'currency_symbol'      => $this->input->post("currency_symbol"),
                            'email'                => $this->input->post("email"),
                            'frontendorbackend'    => true,
                            'frontend_theme'       => 'default',
                            'footer'               => 'Copyright &copy; ' . $this->input->post("sname"),
                            'google_analytics'     => '',
                            'language'             => 'english',
                            'note'                 => 1,
                            'phone'                => $this->input->post("phone"),
                            'photo'                => 'site.png',
                            'purchase_code'        => (isset($purchaseFileRead['purchase_code']) ? $purchaseFileRead['purchase_code'] : ''),
                            'purchase_username'    => (isset($purchaseFileRead['username']) ? $purchaseFileRead['username'] : ''),
                            'school_type'          => 'classbase',
                            'school_year'          => 1,
                            'sname'                => $this->input->post("sname"),
                            'updateversion'        => config_item('ini_version'),
                            'captcha_status'       => 1,
                            'recaptcha_site_key'   => '',
                            'recaptcha_secret_key' => '',
                        ];

                        $array_admin = [
                            'name'              => $this->input->post("adminname"),
                            'dob'               => date('Y-m-d'),
                            'sex'               => 'Male',
                            'religion'          => 'Unknown',
                            'email'             => $this->input->post("email"),
                            'phone'             => '',
                            'address'           => '',
                            'jod'               => date('Y-m-d'),
                            'photo'             => 'defualt.png',
                            'username'          => $this->input->post("username"),
                            'password'          => $this->install_m->hash($this->input->post("password")),
                            'usertypeID'        => 1,
                            'create_date'       => date("Y-m-d h:i:s"),
                            'modify_date'       => date("Y-m-d h:i:s"),
                            'create_userID'     => 0,
                            'create_username'   => $this->input->post("username"),
                            'create_usertype'   => 'Admin',
                            'active'            => 1,
                            'systemadminextra1' => '',
                            'systemadminextra2' => '',
                        ];

                        $array_version = [
                            'version'    => config_item('ini_version'),
                            'date'       => date('Y-m-d H:i:s'),
                            'userID'     => 1,
                            'usertypeID' => 1,
                            'log'        => '<h4>1. initial install</h4>',
                            'status'     => 1,
                        ];

                        $this->install_m->insertorupdate($array);
                        $this->systemadmin_m->update_systemadmin($array_admin, 1);
                        $this->update_m->insert_update($array_version);

                        $sessionData = [
                            'username' => $this->input->post('username'),
                            'password' => $this->input->post('password'),
                        ];
                        $this->session->set_userdata($sessionData);
                        redirect(base_url("install/done"));
                    }
                } else {
                    $this->data["subview"] = "install/site";
                    $this->load->view('_layout_install', $this->data);
                }
            } else {
                redirect(base_url("install/database"));
            }
        } else {
            redirect(base_url("install/purchasecode"));
        }
    }

    public function done()
    {
        $purchaseCodeChecker = $this->_purchaseCodeChecker();
        if (isset($purchaseCodeChecker->status) && $purchaseCodeChecker->status) {
            if ($this->_checkDatabaseConnection()) {
                $this->load->library('session');
                if ($this->session->userdata('username') && $this->session->userdata('password')) {
                    if ($_POST !== []) {
                        $this->config->config_update(["installed" => true]);
                        @chmod($this->config->database_path, FILE_READ_MODE);
                        @chmod($this->config->config_path, FILE_READ_MODE);
                        $this->session->sess_destroy();
                        $file = APPPATH . 'config/purchase.php';
                        if (file_exists($file)) {
                            @chmod($file, FILE_WRITE_MODE);
                            write_file($file, '');
                        }
                        redirect(site_url('signin/index'));
                    } else {
                        $this->data["subview"] = "install/done";
                        $this->load->view('_layout_install', $this->data);
                    }
                } else {
                    redirect(base_url("install/site"));
                }
            } else {
                redirect(base_url("install/database"));
            }
        } else {
            redirect(base_url("install/purchasecode"));
        }
    }

    protected function rules_purchasecode()
    {
        return [
            [
                'field' => 'purchase_username',
                'label' => 'Username',
                'rules' => 'trim|required|max_length[255]|xss_clean|callback_username_validation',
            ],
            [
                'field' => 'purchase_code',
                'label' => 'Purchase Code',
                'rules' => 'trim|required|max_length[255]|xss_clean|callback_purchasecode_validation',
            ],
        ];
    }

    protected function rules_database()
    {
        return [
            [
                'field' => 'host',
                'label' => 'host',
                'rules' => 'trim|required|max_length[255]|xss_clean',
            ],
            [
                'field' => 'database',
                'label' => 'database',
                'rules' => 'trim|required|max_length[255]|xss_clean|callback_database_unique',
            ],
            [
                'field' => 'user',
                'label' => 'user',
                'rules' => 'trim|required|max_length[255]|xss_clean',
            ],
            [
                'field' => 'password',
                'label' => 'password',
                'rules' => 'trim|required|max_length[255]|xss_clean',
            ],
        ];
    }

    protected function rules_timezone()
    {
        return [
            [
                'field' => 'timezone',
                'label' => 'timezone',
                'rules' => 'trim|required|max_length[255]|xss_clean|callback_index_validation',
            ],
        ];
    }

    protected function rules_site()
    {
        return [
            [
                'field' => 'sname',
                'label' => 'Site Name',
                'rules' => 'trim|required|max_length[40]|xss_clean',
            ],
            [
                'field' => 'phone',
                'label' => 'Phone',
                'rules' => 'trim|required|max_length[25]|xss_clean',
            ],
            [
                'field' => 'email',
                'label' => 'Email',
                'rules' => 'trim|required|max_length[40]|xss_clean|valid_email',
            ],
            [
                'field' => 'adminname',
                'label' => 'Admin Name',
                'rules' => 'trim|required|max_length[40]|xss_clean',
            ],
            [
                'field' => 'username',
                'label' => 'Username',
                'rules' => 'trim|required|max_length[40]|xss_clean',
            ],
            [
                'field' => 'password',
                'label' => 'Password',
                'rules' => 'trim|required|max_length[40]|xss_clean',
            ],
        ];
    }

    public function username_validation()
    {
        $array['username']      = $this->input->post('purchase_username');
        $array['purchase_code'] = $this->input->post('purchase_code');
        $apiCurl                = $this->updatechecker->verifyValidUser($array, false);

        if ($apiCurl->status == false && $apiCurl->for == 'username') {
            $this->form_validation->set_message("username_validation", $apiCurl->message);
            return false;
        }
        return true;
    }

    public function purchasecode_validation()
    {
        $array['username']      = $this->input->post('purchase_username');
        $array['purchase_code'] = $this->input->post('purchase_code');
        $apiCurl                = $this->updatechecker->verifyValidUser($array, false);

        if ($apiCurl->status == false && ($apiCurl->for == 'purchasecode' || $apiCurl->for == 'block')) {
            $this->form_validation->set_message("purchasecode_validation", $apiCurl->message);
            return false;
        }
        return true;
    }

    public function database_unique()
    {
        if (trim((string) $this->input->post('host')) && trim((string) $this->input->post('user')) && $this->input->post('password') && trim((string) $this->input->post('database'))) {
            if (strpos((string) $this->input->post('database'), '.') === false) {
                ini_set('display_errors', 'Off');

                $config_db['hostname'] = trim((string) $this->input->post('host'));
                $config_db['username'] = trim((string) $this->input->post('user'));
                $config_db['password'] = $this->input->post('password');
                $config_db['database'] = trim((string) $this->input->post('database'));
                $config_db['dbdriver'] = 'mysqli';

                $this->config->db_config_update($config_db);

                $db_obj = $this->load->database($config_db, true);

              
                $connected = $db_obj->initialize();
                if ($connected) {
                    unset($this->db);
                    $config_db['db_debug'] = false;
                    $this->load->database($config_db);
                    $this->load->dbutil();
                    if ($this->dbutil->database_exists($this->db->database)) {
                        if ($this->db->table_exists('setting') == false) {
                            $encryption_key = md5(config_item('product_name') . uniqid());
                            $this->config->config_update(['encryption_key' => $encryption_key]);
                            $purchaseCodeChecker = $this->_purchaseCodeChecker(['purpose' => 'install']);
                            if (isset($purchaseCodeChecker->status) && $purchaseCodeChecker->status) {
                                $this->load->model('install_m');

                                if (!empty($purchaseCodeChecker->schema)) {
                                    $expSchemas = explode(';', (string) $purchaseCodeChecker->schema);
                                    if (inicompute($expSchemas)) {
                                        foreach ($expSchemas as $expSchema) {
                                            $this->install_m->use_sql_string($expSchema);
                                        }
                                        return true;
                                    } else {
                                        $this->form_validation->set_message("unique_database", "Schema not explode.");
                                        return false;
                                    }
                                } else {
                                    $this->form_validation->set_message("unique_database", "Schema not found.");
                                    return false;
                                }
                            } else {
                                $this->form_validation->set_message("database_unique", "Check internet connection.");
                                return false;
                            }
                        }
                        return true;
                    } else {
                        $this->form_validation->set_message("database_unique", "Database Not Found.");
                        return false;
                    }
                } else {
                    $this->form_validation->set_message("database_unique", "Database Connection Failed.");
                    return false;
                }
            } else {
                $this->form_validation->set_message("database_unique", "Database can not accept dot in DB name.");
                return false;
            }
        } else {
            return false;
        }
    }

    public function index_validation()
    {
        $timezone = $this->input->post('timezone');
        @chmod($this->config->index_path, 0777);
        if (is_really_writable($this->config->index_path) === false) {
            $this->form_validation->set_message("index_validation", "Index file is non-writable.");
            return false;
        } else {
            $file        = $this->config->index_path;
            $filecontent = "date_default_timezone_set('" . $timezone . "');";
            $fileArray   = [2 => $filecontent];
            $this->_replaceLines($file, $fileArray);
            @chmod($this->config->index_path, 0644);
            return true;
        }
    }

    private function _purchaseCodeChecker($data = [])
    {
        $array = $this->_purchaseFileRead();
        if (inicompute($data) && is_array($data)) {
            $array = array_merge($array, $data);
        }
        return $this->updatechecker->verifyValidUser($array, false);
    }

    private function _purchaseFileRead()
    {
        $file = APPPATH . 'config/purchase.php';
        @chmod($file, FILE_WRITE_MODE);
        $purchase = file_get_contents($file);
        $purchase = json_decode($purchase);

        $array = ['purchase_code' => '', 'username' => ''];
        if (is_array($purchase)) {
            $array['purchase_code'] = trim((string) $purchase[1]);
            $array['username']      = trim((string) $purchase[0]);
        }
        return $array;
    }

    private function _replaceLines($file, $new_lines, $source_file = null)
    {
        $response = 0;
        $tab      = chr(9);
        $lbreak   = chr(13) . chr(10);
        $lines = $source_file ? file($source_file) : file($file);
        foreach ($new_lines as $key => $value) {
            $lines[--$key] = $tab . $value . $lbreak;
        }
        $new_content = implode('', $lines);
        if ($h = fopen($file, 'w')) {
            if (fwrite($h, $new_content)) {
                $response = 1;
            }
            fclose($h);
        }
        return $response;
    }

    private function _checkDatabaseConnection()
    {
        ini_set('display_errors', 'Off');
        $getConnectionArray = $this->config->db_config_get();
        $get_obj            = $this->load->database($getConnectionArray, true);
        $connected          = $get_obj->initialize();
        return (bool) $connected;
    }

    private function _checkInternetConnection($sCheckHost = 'www.google.com')
    {
        return (bool) @fsockopen($sCheckHost, 80, $iErrno, $sErrStr, 5);
    }
}
