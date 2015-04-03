<?php defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('display_errors', 'On');
error_reporting(E_ALL);

class Module_model extends CI_Model {
	
	public function __construct(){
		parent::__construct();
		$this->load->database('default');
	}
	
	public function getModuleList(){
		return $this->db->query('SELECT * FROM `libraries`')->result_array();
	}

	public function checkModule($dir){
		$query = $this->db->query("SELECT count(*) as c FROM logicaldrivecontainer ld, clouddrive c, libraries l WHERE c.lid = l.id and ld.cdid = c.cdid and l.dir = ?", array($config->dir));
		$r = $query->row();
		return $r->c;
	}
	
	public function newModule($config){
		$query = $this->db->query('SELECT * FROM `libraries` WHERE `dir` = ?', array($config->dir));
		if($query->num_rows() < 1){
			$sql = "INSERT INTO `jbocd`.`libraries` (`id`, `name`, `dir`, `icon`, `isFA`, version) VALUES (NULL, ?, ?, ?, ?, ?);";
			$this->db->query($sql, array($config->name, $config->dir, $config->icon, $config->isFA, $config->version));
			return "New module $config->name v.$config->version added!";
		}else{
			$row = $query->row();
			$sql = "REPLACE INTO `jbocd`.`libraries` (`id`, `name`, `dir`, `icon`, `isFA`, `version`, `timestamp`) VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
			$this->db->query($sql, array($row->id, $config->name, $config->dir, $config->icon, $config->isFA, $config->version));
			return "New module $config->name (version $config->version) re-initialized!";
		}
	}
	
}

?>