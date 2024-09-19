<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Update extends MY_Controller {
public $load;
 public $lang;
 public $session;
 public $uri;
 public $site_m;
 public $data;
 public $signin_m;
 public $permission_m;
 public $menu_m;
 public $update_m;
 public $input;
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

	protected $_memoryLimit = '1024M';
	protected $_versionCheckingUrl = 'http://demo.inilabs.net/autoupdate/update/index';
	protected $_updateFileUrl = 'http://demo.inilabs.net/autoupdate/updatefiles/itest/';
	protected $_successUrl = 'http://demo.inilabs.net/autoupdate/update/success';
	protected $_downloadPath = FCPATH.'uploads/update';
	protected $_downloadFileWithPath = '';
	protected $_downloadExtractPath = '';

    protected $_backendTheme = '';
    protected $_backendThemePath = '';

	public function __construct()
	{
		parent::__construct();
        $this->load->library("session");
        $this->load->helper('language');
        $this->load->helper('form');
        $this->load->model("update_m");
        $this->load->model("signin_m");

        $this->lang->load('topbar_menu', $this->session->userdata('lang'));
        $this->lang->load('update', $this->session->userdata('lang'));
		if(config_item('demo')) {
			$this->session->set_flashdata('error', 'In demo update module is disable!');
			redirect(base_url('dashboard/index'));
		}

        $this->_adminManager();
	}

    private function _adminManager()
    {
        $this->load->model("permission_m");
        $this->load->model("site_m");
        $this->load->model("schoolyear_m");
        $this->load->model("alert_m");
        $this->load->model("menu_m");
        $this->load->model("usertype_m");

        $module            = $this->uri->segment(1);
        $action            = $this->uri->segment(2);
        $siteInfo          = $this->site_m->get_site();
        $loginManager      = $this->_loginManager();
        $permissionManager = $this->_permissionManager($module, $action);
        if ( !empty($loginManager) ) {
            redirect($loginManager);
        } elseif ( !empty($permissionManager) ) {
            redirect($permissionManager);
        }

        $this->data["siteinfos"]         = $siteInfo;
        $this->_backendTheme             = strtolower((string) $this->data["siteinfos"]->backend_theme);
        $this->_backendThemePath         = 'assets/inilabs/themes/' . strtolower((string) $this->data["siteinfos"]->backend_theme);
        $this->data['backendTheme']      = $this->_backendTheme;
        $this->data['backendThemePath']  = $this->_backendThemePath;
        $this->data['topbarschoolyears'] = $this->schoolyear_m->get_order_by_schoolyear([ 'schooltype' => $this->data["siteinfos"]->school_type ]);
    }

    private function _loginManager()
    {
        $url            = '';
        $exception_uris = [
            "signin/index",
            "signin/signout"
        ];

        if ( in_array(uri_string(), $exception_uris) == false && $this->signin_m->loggedin() == false ) {
            $url = base_url("signin/index");
        }
        return $url;
    }

    private function _permissionManager( $module, $action )
    {
        $permission = $action == 'index' || $action == false ? $module : $module . '_' . $action;

        $url             = '';
        $permissionArray = [];
        $sessionData     = $this->session->userdata;

        if ($this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1) {
            if ( isset($sessionData['loginuserID']) && !isset($sessionData['get_permission']) ) {
                $features = $this->permission_m->get_permission();
                if ( inicompute($features) ) {
                    foreach ( $features as $key => $feature ) {
                        $permissionArray['master_permission_set'][ trim((string) $feature->name) ] = $feature->active;
                    }

                    $data = [ 'get_permission' => true ];
                    $this->session->set_userdata($data);
                    $this->session->set_userdata($permissionArray);
                }
            }
        } elseif (isset($sessionData['loginuserID']) && !isset($sessionData['get_permission'])) {
            if ( !$this->session->userdata($permission) ) {
                $user_permission = $this->permission_m->get_modules_with_permission($sessionData['usertypeID']);
                foreach ( $user_permission as $value ) {
                    $permissionArray['master_permission_set'][ $value->name ] = $value->active;
                }

                if ( $sessionData['usertypeID'] == 3 ) {
                    $permissionArray['master_permission_set']['take_exam'] = 'yes';
                }

                $data = [ 'get_permission' => true ];
                $this->session->set_userdata($data);
                $this->session->set_userdata($permissionArray);
            }
        }

        $sessionPermission     = $this->session->userdata('master_permission_set');
        $dbMenus               = $this->_menuTree(json_decode(json_encode(pluck($this->menu_m->get_order_by_menu([ 'status' => 1 ]),
            'obj', 'menuID')), true), $sessionPermission);
        $this->data["dbMenus"] = $dbMenus;

        if ( ( isset($sessionPermission[ $permission ]) && $sessionPermission[ $permission ] == "no" ) ) {
            if ( $permission == 'dashboard' && $sessionPermission[ $permission ] == "no" ) {
                $url = 'exceptionpage/index';
                if ( in_array('yes', $sessionPermission) && $sessionPermission["dashboard"] == 'no' ) {
                    foreach ( $sessionPermission as $key => $value ) {
                        if ( $value == 'yes' ) {
                            $url = $key;
                            break;
                        }
                    }
                }
            } else {
                $url = base_url('exceptionpage/error');
            }
        }
        return $url;
    }

    public function _menuTree( $dataSet, $sessionPermission )
    {
        $tree = [];
        foreach ( $dataSet as $id => &$node ) {
            if ( $node['link'] == '#' || ( isset($sessionPermission[ $node['link'] ]) && $sessionPermission[ $node['link'] ] != "no" ) ) {
                if ( $node['parentID'] == 0 ) {
                    $tree[ $id ] =& $node;
                } else {
                    if ( !isset($dataSet[ $node['parentID'] ]['child']) ) {
                        $dataSet[ $node['parentID'] ]['child'] = [];
                    }
                    $dataSet[ $node['parentID'] ]['child'][ $id ] = &$node;
                }
            }
        }
        return $tree;
    }

    public function index()
    {
        ini_set('memory_limit', $this->_memoryLimit);
        if ( isset($_FILES["file"]['name']) && $_FILES["file"]['name'] != '' ) {
            $this->htmlDesign('none', false);
            $browseFileUpload = $this->browseFileUpload($_FILES);
            if ( $browseFileUpload->status ) {
                if ( file_exists($this->_downloadFileWithPath) ) {
                    $fileUnZip = $this->fileUnZip();
                    if ( $fileUnZip->status ) {
                        $manageFile = $this->manageFile($browseFileUpload);
                        if ( $manageFile->status ) {
                            $databaseUpdate = $this->databaseUpdate();
                            if ( $databaseUpdate->status ) {
                                if ( $databaseUpdate->version != 'none' ) {
                                    $array = [
                                        'version'    => $databaseUpdate->version,
                                        'date'       => date('Y-m-d H:i:s'),
                                        'userID'     => $this->session->userdata('loginuserID'),
                                        'usertypeID' => $this->session->userdata('usertypeID'),
                                        'status'     => 1,
                                        'log'        => $this->updateLog(),
                                    ];
                                    $this->update_m->insert_update($array);
                                    $this->deleteZipAndFile($this->_downloadFileWithPath);
                                    $this->signin_m->signout();
                                    redirect(base_url("signin/index"));
                                } else {
                                    $this->deleteZipAndFile($this->_downloadFileWithPath);
                                    $this->signin_m->signout();
                                    redirect(base_url("signin/index"));
                                }
                            } else {
                                $this->deleteZipAndFile($this->_downloadFileWithPath);
                                $this->signin_m->signout();
                                redirect(base_url("signin/index"));
                            }
                        } else {
                            $this->session->set_flashdata('error', 'File distribution failed.');
                            redirect(base_url('update/index'));
                        }
                    } else {
                        $this->session->set_flashdata('error', 'File extract failed.');
                        redirect(base_url('update/index'));
                    }
                } else {
                    $this->session->set_flashdata('error', 'Upload file does not exist.');
                    redirect(base_url('update/index'));
                }
            } else {
                $this->session->set_flashdata('error', $browseFileUpload->message);
                redirect(base_url('update/index'));
            }
        } else {
            $this->data['updates'] = $this->update_m->get_update();
            $this->data["subview"] = "update/index";
            $this->load->view('_layout_main', $this->data);
        }
    }

    public function autoupdate()
    {
        ini_set('memory_limit', $this->_memoryLimit);
        if ( $this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1 ) {
            if ( $this->session->userdata('updatestatus') ) {
                if ( inicompute($postData = @$this->postData()) ) {
                    $versionChecking = $this->versionChecking($postData);
                    if ( $versionChecking->status ) {
                        if ( $versionChecking->version != 'none' ) {
                            $this->htmlDesign($versionChecking);
                            $fileDownload = $this->fileDownload($versionChecking);
                            if ( !empty($fileDownload) ) {
                                $filePush = $this->filePush($versionChecking, $fileDownload);
                                if ( $filePush->status ) {
                                    if ( file_exists($this->_downloadFileWithPath) ) {
                                        $fileUnZip = $this->fileUnZip();
                                        if ( $fileUnZip->status ) {
                                            $manageFile = $this->manageFile($versionChecking);
                                            if ( $manageFile->status ) {
                                                $this->databaseUpdate();
                                                $array = [
                                                    'version'    => $versionChecking->version,
                                                    'date'       => date('Y-m-d H:i:s'),
                                                    'userID'     => $this->session->userdata('loginuserID'),
                                                    'usertypeID' => $this->session->userdata('usertypeID'),
                                                    'status'     => 1,
                                                    'log'        => $this->updateLog(),
                                                ];
                                                $this->update_m->insert_update($array);
                                                $this->deleteZipAndFile($this->_downloadFileWithPath);

                                                if ( inicompute($postData) ) {
                                                    $postData['updateversion'] = $versionChecking->version;
                                                    $this->successProvider($postData);
                                                }
                                                $this->signin_m->signout();
                                                redirect(base_url("signin/index"));
                                            } else {
                                                $this->session->set_flashdata('error', 'File distribution failed.');
                                                redirect(base_url('dashboard/index'));
                                            }
                                        } else {
                                            $this->session->set_flashdata('error', 'File extract failed.');
                                            redirect(base_url('dashboard/index'));
                                        }
                                    } else {
                                        $this->session->set_flashdata('error', 'Download file does not exist.');
                                        redirect(base_url('dashboard/index'));
                                    }
                                } else {
                                    $this->session->set_flashdata('error', $filePush->message);
                                    redirect(base_url('dashboard/index'));
                                }
                            } else {
                                $this->session->set_flashdata('error', 'File downloading failed.');
                                redirect(base_url('dashboard/index'));
                            }
                        } else {
                            $this->session->set_flashdata('success', 'You are using the latest version.');
                            redirect(base_url('dashboard/index'));
                        }
                    } else {
                        $this->session->set_flashdata('error', 'Sync update failed.');
                        redirect(base_url('dashboard/index'));
                    }
                } else {
                    $this->session->set_flashdata('error', 'Post data does not found.');
                    redirect(base_url('dashboard/index'));
                }
            } else {
                $this->session->set_flashdata('error', 'Only the main system admin can update this system.');
                redirect(base_url('dashboard/index'));
            }
        } else {
            $this->session->set_flashdata('error', 'Please login via the main system admin.');
            redirect(base_url('dashboard/index'));
        }
    }

    public function getloginfo()
    {
        $text     = '';
        $updateID = $this->input->post('updateID');
        $update   = $this->update_m->get_single_update([ 'updateID' => $updateID ]);
        if ( inicompute($update) ) {
            $text = $update->log;
        }

        echo $text;
    }

    private function browseFileUpload( $file )
    {
        $returnArray['status'] = false;;
        $returnArray['version'] = 'none';
        $returnArray['message'] = 'File not found';

        if ( isset($file['file']) ) {
            $fileName = $file['file']['name'];
            $fileSize = $file['file']['size'];
            $fileTmp  = $file['file']['tmp_name'];
            $fileType = $file['file']['type'];
            $endArray = explode('.', (string) $file['file']['name']);
            $fileExt  = strtolower(end($endArray));

            $extensions  = [ "zip" ];
            $maxFileSize = 1073741824;

            if ( in_array($fileExt, $extensions) ) {
                if ( $fileSize <= $maxFileSize ) {
                    move_uploaded_file($fileTmp, $this->_downloadPath . '/' . $fileName);
                    $this->_downloadFileWithPath = $this->_downloadPath . '/' . $fileName;
                    $returnArray['status']       = true;
                    $returnArray['version']      = str_replace('.zip', '', (string) $fileName);
                    $returnArray['message']      = 'Success';
                } else {
                    $returnArray['message'] = "Set your upload file size at least 1 GB.";
                }
            } else {
                $returnArray['message'] = "Please choose a zip file.";
            }
        }

        return (object) $returnArray;
    }

    private function postData()
    {
        $postData = [];
        $updates  = $this->update_m->get_max_update();
        if ( inicompute($updates) ) {
            $postData = [
                'username'       => inicompute($this->data['siteinfos']) ? $this->data['siteinfos']->purchase_username : '',
                'purchasekey'    => inicompute($this->data['siteinfos']) ? $this->data['siteinfos']->purchase_code : '',
                'domainname'     => base_url(),
                'email'          => inicompute($this->data['siteinfos']) ? $this->data['siteinfos']->email : '',
                'currentversion' => $updates->version,
                'projectname'    => 'itest',
            ];
        }

        return $postData;
    }

    private function versionChecking( $postData )
    {
        $result = [
            'status'  => false,
            'message' => 'Error',
            'version' => 'none'
        ];

        $postDataStrings = json_encode($postData);
        $ch              = curl_init($this->_versionCheckingUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStrings);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postDataStrings)
            ]
        );

        $getResult = curl_exec($ch);
        curl_close($ch);
        if ( inicompute($getResult) ) {
            $result = json_decode($getResult, true);
        }
        return (object) $result;
    }

    private function htmlDesign($versionChecking, $versionShow = TRUE)
	{
		$this->load->config('iniconfig');
		echo '<html>';
			echo '<head>';
				echo '<title>'.$this->lang->line('panel_title').'</title>';
				echo '<link rel="SHORTCUT ICON" href="'.base_url('uploads/images/'.$this->data['siteinfos']->photo).'" />';
				echo '<link href="'.base_url('assets/bootstrap/bootstrap.min.css').'" rel="stylesheet">';
				echo '<link href="'.base_url($this->data['backendThemePath'].'/style.css').'" rel="stylesheet">';
				echo '<link href="'.base_url($this->data['backendThemePath'].'/inilabs.css').'" rel="stylesheet">';
				echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>';
				echo '<style type="text/css">.progress { margin: 10px;max-width: 100%; } .content { padding : 20px; }</style>';
			echo '</head>';
			echo '<body>';
				echo '<div class="content">';
					echo '<div class="row">';
						echo '<div class="col-sm-offset-2 col-sm-8">';
							echo '<div class="jumbotron">';
								echo '<center><p style="font-size:20px"><img style="widht:50px;height:50px" src="'.base_url('uploads/images/'.$this->data['siteinfos']->photo).'"></p></center>';
								echo '<center><p style="font-size:20px;color:#1A2229">'.$this->data['siteinfos']->sname.'</p></center>';
								echo '<center><p style="font-size:14px;color:#1A2229">'.$this->data['siteinfos']->address.'</p></center>';
								if($versionShow) {
									echo '<center><p style="font-size:12px" class="text-green">Your system is updating '.config_item('ini_version').' to '.$versionChecking->version.'</p></center>';
								}
								echo '<center><p style="font-size:12px">-! Please wait some minutes !-</p></center>';

								echo '<div class="progress">';
			  						echo '<div id="dynamic" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">';
			    						echo '<span id="current-progress"></span>';
			  						echo '</div>';
								echo '</div>';

								echo '<p style="font-size:12px;padding-top:10px;padding-left:15px;"> 1. Don\'t close this page</p>';
								echo '<p style="font-size:12px;padding-left:15px;"> 2. Don\'t reload this page</p>';
								echo '<p style="font-size:12px;padding-left:15px;"> 3. Don\'t open another tab of your system</p>';
								echo '<p style="font-size:12px;padding-left:15px;"> 4. When the update process will be complete it will redirect to the sign-in page</p>';
								echo '<script type="text/javascript">';
									echo '$(function() {
									  	var current_progress = 0;
									  	var interval = setInterval(function() {
									    	current_progress += 1;
									    	$("#dynamic")
									    	.css("width", current_progress + "%")
									    	.attr("aria-valuenow", current_progress)
									    	.text(current_progress + "% Complete");
									    	if (current_progress >= 100)
									        	clearInterval(interval);
									  	}, 18000);
									});';
								echo '</script>';
							echo '</div>';
						echo '</div>';
					echo '</div>';
				echo '</div>';
			echo '</body>';
		echo '</html>';
	}

    private function fileDownload( $result )
    {
        ini_set('memory_limit', $this->_memoryLimit);
        $this->_updateFileUrl = $this->_updateFileUrl . $result->version . '.zip';
        $curlCh               = curl_init();
        curl_setopt($curlCh, CURLOPT_URL, $this->_updateFileUrl);
        curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlCh, CURLOPT_SSLVERSION, 3);
        $curlData = curl_exec($curlCh);
        curl_close($curlCh);
        return $curlData;
    }

    private function filePush( $result, $curlData )
    {
        $returnArray['status']  = false;
        $returnArray['message'] = 'Error';
        $downloadPath           = FCPATH . 'uploads/update/' . $result->version . '.zip';
        $permissionCheckingPath = FCPATH . 'uploads/update/index.html';
        if ( file_exists($permissionCheckingPath) ) {
            try {
                if ( $file = @fopen($downloadPath, 'w+') ) {
                    fwrite($file, (string) $curlData);
                    fclose($file);

                    $this->_downloadFileWithPath = $downloadPath;
                    $returnArray['status']       = true;
                    $returnArray['message']      = 'Success';
                } else {
                    $returnArray['message'] = 'Uploads folder permission has the decline.';
                }
            } catch ( Exception $e ) {
                $returnArray['message'] = 'Uploads folder permission has the decline.';
            }
        } else {
            $returnArray['message'] = 'Uploads folder permission has the decline.';
        }

        return (object) $returnArray;
    }

    private function fileUnZip()
    {
        $returnArray['status']  = false;
        $returnArray['message'] = 'Error';
        $zip                    = new ZipArchive;
        if ( $zip->open($this->_downloadFileWithPath) === true ) {
            $zip->extractTo($this->_downloadPath);
            $zip->close();
            $returnArray['status']  = true;
            $returnArray['message'] = 'Success';
        } else {
            $returnArray['message'] = 'The update zip does not found.';
        }

        return (object) $returnArray;
    }

    private function manageFile( $versionChecking )
    {
        $returnArray['status']      = false;
        $returnArray['message']     = 'File distribution fail';
        $destination                = FCPATH;
        $destination                = rtrim((string) $destination, '/');
        $this->_downloadExtractPath = $this->_downloadPath . '/' . $versionChecking->version . '/';
        if ( $this->smartCopy($this->_downloadExtractPath, $destination) ) {
            $returnArray['status']  = true;
            $returnArray['message'] = 'Success';
        }

        return (object) $returnArray;
    }

    private function smartCopy( $source, $dest, $options = [ 'folderPermission' => 0777, 'filePermission' => 0777 ] )
    {
        $result = false;
        if ( is_file($source) ) {
            if ( $dest[ strlen((string) $dest) - 1 ] == '/' ) {
                if ( !file_exists($dest) ) {
                    cmfcDirectory::makeAll($dest, $options['folderPermission'], true);
                }
                $__dest = $dest . "/" . basename((string) $source);
            } else {
                $__dest = $dest;
            }
            $result = copy($source, $__dest);
            @chmod($__dest, $options['filePermission']);
        } elseif ( is_dir($source) ) {
            if ($dest[ strlen((string) $dest) - 1 ] == '/') {
                if ( $source[ strlen((string) $source) - 1 ] == '/' ) {
                    //Copy only contents
                } else {
                    $dest .= basename((string) $source);
                    @mkdir($dest);
                    @chmod($dest, $options['filePermission']);
                }
            } elseif ($source[ strlen((string) $source) - 1 ] == '/') {
                @mkdir($dest, $options['folderPermission']);
                @chmod($dest, $options['filePermission']);
            } else {
                @mkdir($dest, $options['folderPermission']);
                @chmod($dest, $options['filePermission']);
            }

            $dirHandle = opendir($source);
            while ( $file = readdir($dirHandle) ) {
                if ( $file != "." && $file != ".." ) {
                    $__dest = is_dir($source . "/" . $file) ? $dest . "/" . $file : $dest . "/" . $file;
                    $result = $this->smartCopy($source . "/" . $file, $__dest, $options);
                }
            }
            closedir($dirHandle);
        } else {
            $result = false;
        }
        return $result;
    }

    private function databaseUpdate()
    {
        $returnArray['status']  = false;
        $returnArray['version'] = 'none';
        $returnArray['message'] = 'Unknown version';

        if ( file_exists($this->_downloadExtractPath . 'inilabs.json') ) {
            $string = file_get_contents($this->_downloadExtractPath . 'inilabs.json');
            if ( $string !== '' && $string !== false ) {
                $jsonArray = json_decode($string, true);
                if ( isset($jsonArray['database']['status']) && strtolower((string) $jsonArray['database']['status']) != 'no' && (isset($jsonArray['filename']) && !empty($jsonArray['filename'])) ) {
                    $this->sqlGenerator($jsonArray['filename']);
                }

                if ( isset($jsonArray['version']) && !empty($jsonArray['version']) ) {
                    $returnArray['status']  = true;
                    $returnArray['version'] = $jsonArray['version'];
                    $returnArray['message'] = 'Success';
                }
            } else {
                $returnArray['message'] = 'inilabs.json content is empty.';
            }
        } else {
            $returnArray['message'] = 'inilabs.json file not found.';
        }
        return (object) $returnArray;
    }

    private function sqlGenerator( $filename )
    {
        if ( !empty($filename) ) {
            $file = APPPATH . 'libraries/upgrade/' . $filename . '.php';
            if ( file_exists($file) && is_file($file) ) {
                @include_once( $file );
            }
        }
    }

    private function updateLog()
    {
        $string = file_get_contents($this->_downloadExtractPath . 'inilabs.log');
        if ( $string !== '' && $string !== false ) {
            return $string;
        }
        return '';
    }

    private function deleteZipAndFile( $filePathAndName )
    {
        $returnArray['status']  = false;
        $returnArray['message'] = 'Error';

        try {
            if ( file_exists($filePathAndName) ) {
                unlink($filePathAndName);
                $filePathAndName = str_replace(".zip", "", (string) $filePathAndName);
                $this->rmdirRecursive($filePathAndName);
                $this->EmptyFolder(APPPATH . 'libraries/upgrade/');
            }

            $returnArray['status']  = true;
            $returnArray['message'] = 'Success';
        } catch ( Exception $e ) {
            $returnArray['message'] = 'File delete permission problem.';
        }

        return (object) $returnArray;
    }

    private function rmdirRecursive( $dir )
    {
        if ( !file_exists($dir) ) {
            return true;
        }

        if ( !is_dir($dir) ) {
            return unlink($dir);
        }

        foreach ( scandir($dir) as $item ) {
            if ( $item == '.' || $item == '..' ) {
                continue;
            }

            if ( !$this->rmdirRecursive($dir . DIRECTORY_SEPARATOR . $item) ) {
                return false;
            }
        }

        return rmdir($dir);
    }

    private function EmptyFolder( $dir )
    {
        foreach ( scandir($dir) as $item ) {
            if ( $item == '.' || $item == '..' ) {
                continue;
            }
            unlink($dir . $item);
        }
        return true;
    }

    private function successProvider( $postData )
    {
        $result = [
            'status'  => false,
            'message' => 'Error'
        ];

        $postDataStrings = json_encode($postData);
        $ch              = curl_init($this->_successUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataStrings);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postDataStrings)
            ]
        );

        $getResult = curl_exec($ch);
        curl_close($ch);
        if ( inicompute($getResult) ) {
            $result = json_decode($getResult, true);
        }
        return (object) $result;
    }
}
