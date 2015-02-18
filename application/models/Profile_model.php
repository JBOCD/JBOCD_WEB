<?php defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('display_errors', 'On');
error_reporting(E_ALL);

class Profile_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->load->database('default');
	}
	
	public function getProfile($id){
		if(!$id) return null;
		$query = $this->db->query('SELECT * FROM `profile` WHERE `id` = ?', array($id));
		if($query->num_rows() > 0){
			return $query->row();
		} else {
			return false;
		}
		
	}
	
}