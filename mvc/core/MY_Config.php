<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Config Extends CI_Config {
	public $CI;
 public $session;
 public $config;
 /**
  * @var string
  */
 public $config_path 		= ''; // Set in the constructor below
 /**
  * @var string
  */
 public $database_path		= ''; // Set in the constructor below
 /**
  * @var string
  */
 public $index_path			= ''; // Set in the constructor below
 /**
  * @var string
  */
 public $autoload_path		= ''; // Set in the constructor below
    /**
     * @var string
     */
    public $purchase_path      = ''; // Set in the constructor below

    public function __construct()
    {
        parent::__construct();
        $this->config_path   = APPPATH . 'config/config' . EXT;
        $this->database_path = APPPATH . 'config/database' . EXT;
        $this->autoload_path = APPPATH . 'config/autoload' . EXT;
        $tem_index           = getcwd();
        $this->index_path    = $tem_index . "/index.php";
        $this->purchase_path = APPPATH . 'config/purchase' . EXT;
    }

    public function config_update( $config_array = [] )
    {
        if ( !is_array($config_array) && inicompute($config_array) == 0 ) {
            return false;
        }

        @chmod($this->config_path, FILE_WRITE_MODE);

        // Is the config file writable?
        if ( !is_really_writable($this->config_path) ) {
            show_error($this->config_path . ' does not appear to have the proper file permissions.  Please make the file writeable.');
        }

        // Read the config file as PHP
        require "$this->config_path";

        // load the file helper
        $this->CI =& get_instance();
        $this->CI->load->helper('file');

        // Read the config data as a string
        $config_file = read_file($this->config_path);

        // Trim it
        $config_file = trim((string) $config_file);

        // Do we need to add totally new items to the config file?
        if ( is_array($config_array) ) {
            foreach ( $config_array as $key => $val ) {
                $pattern = '/\$config\[\\\'' . $key . '\\\'\]\s+=\s+[^\;]+/';

                if ( gettype($val) == 'string' ) {
                    $replace = "\$config['$key'] = '$val'";
                } elseif ( gettype($val) == 'boolean' ) {
                    $val = $val ? 'TRUE' : 'FALSE';
                    $replace = "\$config['$key'] = $val";
                } else {
                    $replace = "\$config['$key'] = $val";
                }

                $config_file = preg_replace($pattern, $replace, $config_file);
            }
        }

        if ( !$fp = fopen($this->config_path, FOPEN_WRITE_CREATE_DESTRUCTIVE) ) {
            return false;
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $config_file, strlen($config_file));
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($this->config_path, FILE_READ_MODE);

        return true;
    }

    public function db_config_update( $dbconfig = [], $remove_values = [] )
    {
        @chmod($this->database_path, FILE_WRITE_MODE);

        // Is the database file writable?
        if ( !is_really_writable($this->database_path) ) {
            show_error($this->database_path . ' does not appear to have the proper file permissions.  Please make the file writeable.');
        }

        // load the file helper
        $this->CI =& get_instance();
        $this->CI->load->helper('file');

        // Read the config file as PHP
        require "$this->database_path";

        // Now we read the file data as a string
        $config_file = read_file($this->database_path);

        if ( inicompute($dbconfig) > 0 ) {
            foreach ( $dbconfig as $key => $val ) {
                $pattern     = '/\$db\[\\\'' . $active_group . '\\\'\]\[\\\'' . $key . '\\\'\]\s+=\s+[^\;]+/';
                $replace     = "\$db['$active_group']['$key'] = '$val'";
                $config_file = preg_replace($pattern, $replace, (string) $config_file);

            }
        }

        $config_file = trim((string) $config_file);

        // Write the file
        if ( !$fp = fopen($this->database_path, FOPEN_WRITE_CREATE_DESTRUCTIVE) ) {
            return false;
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $config_file, strlen($config_file));
        flock($fp, LOCK_UN);
        fclose($fp);

        @chmod($this->database_path, FILE_READ_MODE);

        return true;
    }

    public function db_config_get()
    {
        @chmod($this->database_path, FILE_WRITE_MODE);

        // Is the database file writable?
        if ( !is_really_writable($this->database_path) ) {
            show_error($this->database_path . ' does not appear to have the proper file permissions.  Please make the file writeable.');
        }

        // load the file helper
        $this->CI =& get_instance();
        $this->CI->load->helper('file');

        // Read the config file as PHP
        require $this->database_path;

        $array = [];
        $file  = $this->database_path;
        $items = [
            '[\'hostname\']',
            '[\"hostname\"]',
            '[\'username\']',
            '[\"username\"]',
            '[\'password\']',
            '[\"password\"]',
            '[\'database\']',
            '[\"database\"]',
        ];

        $contents = file_get_contents($file);

        foreach ( $items as $item ) {
            $pattern = preg_quote($item, '/');
            $pattern = "/^.*$pattern.*\$/m";
            if ( preg_match_all($pattern, $contents, $matche) ) {
                foreach ( $matche as $matcheAllDataKey => $matcheAllData ) {
                    foreach ( $matcheAllData as $matcheAllDataKey => $value ) {
                        $matche       = trim((string) $value);
                        $matchReplace = str_replace([ ';', ' ' ], [ '', '' ], $matche);
                        $expitem      = explode("=", $matchReplace);
                        if ( inicompute($expitem) >= 2 ) {
                            $expitemone           = str_replace([ "'", '"', '[', ']' ], [ '', '', '', '' ], $item);
                            $expitemtwo           = str_replace([ "'", '"' ], [ '', '' ], $expitem[1]);
                            $array[ $expitemone ] = $expitemtwo;
                        }
                    }
                }
            }
        }
        $array['dbdriver'] = 'mysqli';
        $array['db_debug'] = false;
        return $array;
    }

    public function config_status()
    {
        $data['install_warnings'] = [];

        // is PHP version ok?
        if ( !is_php('5.1.6') ) {
            $data['install_warnings'][] = 'php version is too old';
        }

        // is config file writable?
        if ( is_really_writable($this->config_path) && !@chmod($this->config_path, FILE_WRITE_MODE) ) {
            $data['install_warnings'][] = 'config.php file is not writable';
        }

        // Is there a database.php file?
        if ( @include( $this->database_path ) ) {
            if ( $this->test_mysql_connection($db[ $active_group ]) ) {
                $this->session->set_userdata('user_database_file', true);
            } else {
                // Ensure the session isn't remembered from a previous test
                $this->session->set_userdata('user_database_file', false);

                @chmod($this->config->database_path, FILE_WRITE_MODE);

                if ( is_really_writable($this->config->database_path) === false ) {
                    $vars['install_warnings'][] = 'database file is not writable';
                }
            }
        } else {
            $data['install_warnings'][] = 'database config file was not found';
        }

        return $data;
    }

    public function config_install()
    {
        $file  = fopen($this->config_path, "r");
        $newAR = true;
        while ( !feof($file) ) {
            $string      = preg_replace('/\s+/', '', fgets($file));
            $mypattern[] = '$config[\'installed\']=FALSE;';
            $mypattern[] = '$config[\'installed\']=False;';
            $mypattern[] = '$config[\'installed\']=false;';
            $mypattern[] = '$config[\'installed\']=0;';

            foreach ( $mypattern as $pattern ) {
                if ( $pattern == $string ) {
                    $newAR = false;
                }
            }

        }
        fclose($file);
        return $newAR;
    }
}


