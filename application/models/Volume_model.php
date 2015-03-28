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

	public function getVolumeListByDrive($uid, $cdid){
		$this->db->select('*');
		$this->db->where('uid', $uid);
		$this->db->where_in('cdid', $cdid);
		$query = $this->db->get('volumesView');

		if($query->num_rows() > 0){
			return $query->result();
		}
		return false;
	}

	public function createVolume($cd, $algo, $uid, $name, $size){

		$this->db->trans_start();

		$data = array(
			'uid'=>$uid,
			'algoid'=>$algo,
			'name'=>$name,
			'size'=>$size
		);
		$this->db->insert('logicaldriveinfo', $data);
		$id = $this->db->insert_id();

		foreach ($cd as $key => $container) {
			$con = array(
				'ldid'=>$id,
				'cdid'=>$key,
				'cddir'=>"/JBOCD/$id/",
				'size'=>$size * 1024 * 1024,
				'alloc_size'=>$container * 1024 * 1024
			);
			$this->db->insert('logicaldrivecontainer', $con);
		}

		$this->db->trans_complete();

		if ($this->db->trans_status()){
			echo json_encode(array('status'=>0));
		}else{
			echo json_encode(array('status'=>1, 'message'=>'Database error!'));
		}

	}

	public function getLogicalVolumeList($uid){
		$list = array();
		$this->db->select('*');
		$this->db->where('uid', $uid);
		$query = $this->db->get('logicaldriveinfo');
		return $query->result();
	}

}

?>