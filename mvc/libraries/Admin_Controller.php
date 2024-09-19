<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @property document_m $document_m
 * @property email_m $email_m
 * @property error_m $error_m
 */
class Admin_Controller extends MY_Controller {
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
	
	private $_backendTheme = '';
	private $_backendThemePath = '';

    public function __construct()
    {
        parent::__construct();
        $this->load->model("signin_m");
        $this->load->model("permission_m");
        $this->load->model("site_m");
        $this->load->model("schoolyear_m");
        $this->load->model("alert_m");
        $this->load->model("menu_m");
        $this->load->model('usertype_m');
        $this->load->model("section_m");
        $this->load->library("session");
        $this->load->helper('language');
        $this->load->helper('date');
        $this->load->helper('form');
        $this->load->library('form_validation');
        $this->lang->load('topbar_menu', $this->session->userdata('lang'));

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
        $this->_backendTheme             = strtolower($this->data["siteinfos"]->backend_theme);
        $this->_backendThemePath         = 'assets/inilabs/themes/' . strtolower($this->data["siteinfos"]->backend_theme);
        $this->data['backendTheme']      = $this->_backendTheme;
        $this->data['backendThemePath']  = $this->_backendThemePath;
        $this->data['topbarschoolyears'] = $this->schoolyear_m->get_order_by_schoolyear([ 'schooltype' => $this->data["siteinfos"]->school_type ]);
        $this->data['allcountry']        = $this->_country();
        $this->data['allbloodgroup']     = $this->_bloodGroup();
    }

    private function _loginManager()
    {
        $url            = '';
        $exception_uris = [
            "signin/index",
            "signin/signout"
        ];

        if ( in_array(uri_string(), $exception_uris) == false ) {
            if ( $this->signin_m->loggedin() == false ) {
                $url = base_url("signin/index");
            }
        }
        return $url;
    }

    private function _permissionManager( $module, $action )
    {
        if ( $action == 'index' || $action == false ) {
            $permission = $module;
        } else {
            $permission = $module . '_' . $action;
        }

        $url             = '';
        $permissionArray = [];
        $sessionData     = $this->session->userdata;

        if ( $this->session->userdata('usertypeID') == 1 && $this->session->userdata('loginuserID') == 1 ) {
            if ( isset($sessionData['loginuserID']) && !isset($sessionData['get_permission']) ) {
                $features = $this->permission_m->get_permission();
                if ( inicompute($features) ) {
                    foreach ( $features as $key => $feature ) {
                        $permissionArray['master_permission_set'][ trim($feature->name) ] = $feature->active;
                    }

                    $data = [ 'get_permission' => true ];
                    $this->session->set_userdata($data);
                    $this->session->set_userdata($permissionArray);
                }
            }
        } else {
            if ( isset($sessionData['loginuserID']) && !isset($sessionData['get_permission']) ) {
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
        }

        $sessionPermission     = $this->session->userdata('master_permission_set');
        $dbMenus               = $this->_menuTree(json_decode(json_encode(pluck($this->menu_m->get_order_by_menu([ 'status' => 1]),
            'obj', 'menuID')), true), $sessionPermission);
        $this->data["dbMenus"] = $dbMenus;

        if ( ( isset($sessionPermission[ $permission ]) && $sessionPermission[ $permission ] == "no" ) ) {
            if ( $permission == 'dashboard' && $sessionPermission[ $permission ] == "no" ) {
                $url = 'exceptionpage/index';
                if ( in_array('yes', $sessionPermission) ) {
                    if ( $sessionPermission["dashboard"] == 'no' ) {
                        foreach ( $sessionPermission as $key => $value ) {
                            if ( $value == 'yes' ) {
                                $url = $key;
                                break;
                            }
                        }
                    }
                }
            } else {
                $url = base_url('exceptionpage/error');
            }
        }
        return $url;
    }

    public function usercreatemail( $email = null, $username = null, $password = null )
    {
        $this->load->model('emailsetting_m');
        $this->load->library('email');
        $this->email->set_mailtype("html");

        if ( !empty($email) ) {
            $emailsetting = $this->emailsetting_m->get_emailsetting();
            if ( $emailsetting->email_engine == 'smtp' ) {
                if ($emailsetting->smtp_security){
                    $config = [
                        'protocol'    => 'smtp',
                        'smtp_host'   => $emailsetting->smtp_server,
                        'smtp_port'   => $emailsetting->smtp_port,
                        'smtp_user'   => $emailsetting->smtp_username,
                        'smtp_pass'   => $emailsetting->smtp_password,
                        'smtp_crypto' => $emailsetting->smtp_security,
                        'mailtype'    => 'html',
                        'charset'     => 'utf-8',
                        'crlf' => "\r\n",
                        'newline' => "\r\n"
                    ];
                } else{
                    $config = [
                        'protocol'    => 'smtp',
                        'smtp_host'   => $emailsetting->smtp_server,
                        'smtp_port'   => $emailsetting->smtp_port,
                        'smtp_user'   => $emailsetting->smtp_username,
                        'smtp_pass'   => $emailsetting->smtp_password,
                        'mailtype'    => 'html',
                        'charset'     => 'utf-8',
                        'crlf' => "\r\n",
                        'newline' => "\r\n"
                    ];
                }
                $this->email->initialize($config);
            }

            $fromEmail = $this->data["siteinfos"]->email;
            $this->email->from($fromEmail, $this->data['siteinfos']->sname);
            $this->email->to($email);
            $this->email->subject($this->data['siteinfos']->sname);
            $url     = base_url();
            $message = "<h2>Welcome to " . $this->data['siteinfos']->sname . "</h2>
	        <p>Please log-in to this website and change the password as soon as possible </p>
	        <p>Website : " . $url . "</p>
	        <p>Username: " . $username . "</p>
	        <p>Password: " . $password . "</p>
	        <br>
	        <p>Once again, thank you for choosing " . $this->data['siteinfos']->sname . "</p>
	        <p>Best Wishes,</p>
	        <p>The " . $this->data['siteinfos']->sname . " Team</p>";
            $this->email->message($message);
            $this->email->send();
        }
    }

    public function reportPDF(
        $stylesheet = null,
        $data = null,
        $viewpath = null,
        $mode = 'view',
        $pagesize = 'a4',
        $pagetype = 'portrait'
    ) {
        $designType = 'LTR';
        $this->data['panel_title'] = $this->lang->line('panel_title');
        $html = $this->load->view($viewpath, $this->data, true);

        $this->load->library('mhtml2pdf');

        $this->mhtml2pdf->folder('uploads/report/');
        $this->mhtml2pdf->filename('Report');
        $this->mhtml2pdf->paper($pagesize, $pagetype);
        $this->mhtml2pdf->html($html);

        if ( !empty($stylesheet) ) {
            $stylesheet = file_get_contents(base_url('assets/pdf/' . $designType . '/' . $stylesheet));
            return $this->mhtml2pdf->create($mode, $this->data['panel_title'], $stylesheet);
        } else {
            return $this->mhtml2pdf->create($mode, $this->data['panel_title'], '');
        }
    }

    public function reportSendToMail(
        $stylesheet = null,
        $data = null,
        $viewpath = null,
        $email = null,
        $subject = null,
        $message = null,
        $pagesize = 'a4',
        $pagetype = 'portrait'
    ) {
        $this->load->model('emailsetting_m');

        $designType = 'LTR';
        $this->load->library('email');
        $this->load->library('mhtml2pdf');
        $this->mhtml2pdf->folder('uploads/report/');
        $rand    = random19() . date('y-m-d h:i:s');
        $sharand = hash('sha512', $rand);

        $this->mhtml2pdf->filename($sharand);
        $this->mhtml2pdf->paper($pagesize, $pagetype);
        $this->data['panel_title'] = $this->lang->line('panel_title');
        $html                      = $this->load->view($viewpath, $this->data, true);
        $this->mhtml2pdf->html($html);

        if ( !empty($stylesheet) ) {
            $stylesheet = file_get_contents(base_url('assets/pdf/' . $designType . '/' . $stylesheet));
        }

        $emailsetting = $this->emailsetting_m->get_emailsetting();
        $this->email->set_mailtype("html");

        if ( inicompute($emailsetting) ) {
            if ( $path = @$this->mhtml2pdf->create('save', $this->data['panel_title'], $stylesheet) ) {
                if ( $emailsetting->email_engine == 'smtp' ) {

                    if ($emailsetting->smtp_security){
                        $config = [
                            'protocol'    => 'smtp',
                            'smtp_host'   => $emailsetting->smtp_server,
                            'smtp_port'   => $emailsetting->smtp_port,
                            'smtp_user'   => $emailsetting->smtp_username,
                            'smtp_pass'   => $emailsetting->smtp_password,
                            'smtp_crypto' => $emailsetting->smtp_security,
                            'mailtype'    => 'html',
                            'charset'     => 'utf-8',
                            'crlf' => "\r\n",
                            'newline' => "\r\n"
                        ];
                    } else{
                        $config = [
                            'protocol'    => 'smtp',
                            'smtp_host'   => $emailsetting->smtp_server,
                            'smtp_port'   => $emailsetting->smtp_port,
                            'smtp_user'   => $emailsetting->smtp_username,
                            'smtp_pass'   => $emailsetting->smtp_password,
                            'mailtype'    => 'html',
                            'charset'     => 'utf-8',
                            'crlf' => "\r\n",
                            'newline' => "\r\n"
                        ];
                    }


                    try {
                        $this->email->initialize($config);
                    } catch ( Exception $e ) {
                        $this->session->set_flashdata('error', $this->lang->line('mail_error'));
                    }
                }

                $fromEmail = $this->data["siteinfos"]->email;
                if ( $this->session->userdata('email') != '' ) {
                    $fromEmail = $this->session->userdata('email');
                }

                try {
                    $this->email->from($fromEmail, $this->data['siteinfos']->sname);
                    $this->email->to($email);
                    $this->email->subject($subject);
                    $this->email->message($message);
                    $this->email->attach($path);

                    if ( $this->email->send() ) {
                        $this->session->set_flashdata('success', $this->lang->line('mail_success'));
                    } else {
                        $this->session->set_flashdata('error', $this->lang->line('mail_error'));
                    }
                } catch ( Exception $e ) {
                    $this->session->set_flashdata('error', $this->lang->line('mail_error'));
                }

            }
        } else {
            $this->session->set_flashdata('error', $this->lang->line('mail_error'));
        }
    }

    public function _country()
    {
        $country = [
            "AF" => "Afghanistan",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "BQ" => "British Antarctic Territory",
            "IO" => "British Indian Ocean Territory",
            "VG" => "British Virgin Islands",
            "BN" => "Brunei",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CT" => "Canton and Enderbury Islands",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos [Keeling] Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo - Brazzaville",
            "CD" => "Congo - Kinshasa",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "CI" => "Côte d’Ivoire",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "NQ" => "Dronning Maud Land",
            "DD" => "East Germany",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "FQ" => "French Southern and Antarctic Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GG" => "Guernsey",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and McDonald Islands",
            "HN" => "Honduras",
            "HK" => "Hong Kong SAR China",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IM" => "Isle of Man",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JE" => "Jersey",
            "JT" => "Johnston Island",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Laos",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macau SAR China",
            "MK" => "Macedonia",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "FX" => "Metropolitan France",
            "MX" => "Mexico",
            "FM" => "Micronesia",
            "MI" => "Midway Islands",
            "MD" => "Moldova",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "ME" => "Montenegro",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar [Burma]",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NT" => "Neutral Zone",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "KP" => "North Korea",
            "VD" => "North Vietnam",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PC" => "Pacific Islands Trust Territory",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territories",
            "PA" => "Panama",
            "PZ" => "Panama Canal Zone",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "YD" => "People's Democratic Republic of Yemen",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn Islands",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RO" => "Romania",
            "RU" => "Russia",
            "RW" => "Rwanda",
            "RE" => "Réunion",
            "BL" => "Saint Barthélemy",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "MF" => "Saint Martin",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "RS" => "Serbia",
            "CS" => "Serbia and Montenegro",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and the South Sandwich Islands",
            "KR" => "South Korea",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syria",
            "ST" => "São Tomé and Príncipe",
            "TW" => "Taiwan",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UM" => "U.S. Minor Outlying Islands",
            "PU" => "U.S. Miscellaneous Pacific Islands",
            "VI" => "U.S. Virgin Islands",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "SU" => "Union of Soviet Socialist Republics",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "ZZ" => "Unknown or Invalid Region",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VA" => "Vatican City",
            "VE" => "Venezuela",
            "VN" => "Vietnam",
            "WK" => "Wake Island",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe",
            "AX" => "Åland Islands",
        ];
        return $country;
    }

    private function _bloodGroup()
    {
        $bloodGroup = [
            'A+'  => 'A+',
            'A-'  => 'A-',
            'B+'  => 'B+',
            'B-'  => 'B-',
            'O+'  => 'O+',
            'O-'  => 'O-',
            'AB+' => 'AB+',
            'AB-' => 'AB-'
        ];
        return $bloodGroup;
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
}

