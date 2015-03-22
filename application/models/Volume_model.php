<?php defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('display_errors', 'On');
error_reporting(E_ALL);

class Volume_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->load->database('default');
	}

	public function getVolumeList($uid){
		$query = $this->db->query('SELECT * FROM `volumesView` WHERE `uid` = ?', array($uid));
		if($query->num_rows() > 0){
			return $query->result();
		}
		return false;
	}

}

?>