<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Menu_m extends MY_Model {

	protected $_table_name = 'menu';
	protected $_primary_key = 'menuID';
	protected $_primary_filter = 'intval';
	protected $_order_by = "priority desc";

	public function __construct() 
	{
		parent::__construct();
	}

	public function get_menu($array=NULL, $signal=FALSE) 
	{
		$query = parent::get($array, $signal);
		return $query;
	}

	public function get_order_by_menu($array=NULL) 
	{
		$query = parent::get_order_by($array);
		return $query;
	}

	public function insert_menu($array) 
	{
		$error = parent::insert($array);
		return TRUE;
	}

	public function update_menu($data, $id = NULL) 
	{
		parent::update($data, $id);
		return $id;
	}

	public function delete_menu($id){
		parent::delete($id);
	}
}