<?php defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('display_errors', 'On');
error_reporting(E_ALL);

class Algorithm_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->load->database('default');
	}

	public function getAlgorithmList(){
		$query = $this->db->query('SELECT * FROM `algorithm`');
		if($query->num_rows() > 0){
			return $query->result();
		}
		return false;
	}

	public function getAlgorithm($id){
		$query = $this->db->query('SELECT * FROM `algorithm` WHERE `id` = ?', array($id));
		if($query->num_rows() > 0){
			return $query->result();
		}
		return false;
	}

}
