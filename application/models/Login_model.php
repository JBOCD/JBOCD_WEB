<?php defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('display_errors', 'On');
error_reporting(E_ALL);

class Login_model extends CI_Model {

	public function __construct(){
		parent::__construct();
		$this->load->database('default');
	}
	
	public function refreshToken($id){
		$this->db->query('REPLACE INTO `token` (`id`,`timestamp`) VALUES(?, CURRENT_TIMESTAMP)', array($id));
		return $id;
	}
	
	public function authenticate($login, $pw){
		if(!isset($login) || !isset($pw) || ($login == '') || ($pw=='')) {
			$this->session->set_userdata('login_error', array('message'=>'Login error!'));
			return -2;
		}
		if(($login == '') || ($pw=='')) return -2;
		$result = $this->db->query('SELECT * FROM `auth` WHERE `login` = ?', array($login));
		if($result->num_rows() == 1){
			$result = $result->row();
			if(hash('sha1', $pw) == $result->pw) {
				$this->session->set_userdata('login_data', array('id'=>$this->login_model->refreshToken($result->id)));
				return $result->id;
			}
			$this->session->set_userdata('login_error', array('message'=>'Please enter login credentials!'));
			return false;
		} else {
			$this->session->set_userdata('login_error', array('message'=>'Please enter login credentials!'));
			return false;
		}
	}
	
	public function isAuthenticated(){
		if($data = $this->session->userdata('login_data')){
			$query = $this->db->query('SELECT * FROM `token` WHERE `id` = ?', array($data['id']));
			if($query->num_rows() == 1){
				$result = $query->row();
				$ts = new DateTime($result->timestamp);
				$now = new DateTime();
				if(($now->getTimestamp() - $ts->getTimestamp()) >= 1440){
					return false;
				}else{
					return $data['id'];
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	public function revokeToken(){
		if($this->session->userdata('login_data')){
			$data = $this->session->userdata('login_data');
			$this->db->query('DELETE FROM `token` WHERE `id` = ?', array($data['id']));
			$this->session->unset_userdata('login_data');
		}
	}
}

?>