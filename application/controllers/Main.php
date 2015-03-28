<?php defined('BASEPATH') OR exit('No direct script access allowed');



class Main extends CI_Controller {

	private $python_module_path;
	private $php_module_path;

	public function __construct(){
		parent::__construct();
		$this->php_module_path = APPPATH."third_party/";
		$this->python_module_path = "/var/JBOCD/module/";

		session_start();
		$this->load->model('view_model');
		$this->load->model('login_model');
		if(!$this->login_model->isAuthenticated()) {
			return redirect('login/index', 'location');
		}else{
			$this->login_model->refreshToken($this->session->userdata('login_data'));
		} //
		
	}

	public function index(){
		$this->load->model('module_model');
		$data['modules'] = $this->module_model->getModuleList();
		$this->view_model->generateView($this->load->view('content', $data, true));
	}
	
	public function installModule(){
		$mViewData['title'] = "Module installation";
		$mViewData['returnLink'] = site_url('main/module');
		$mViewData['message'] = "";
	
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'zip';
		$config['overwrite'] = TRUE;
		$config['encrypt_name'] = TRUE;
		
		$this->load->library('upload', $config);
		//$this->load->library('zip');
		$zip = new ZipArchive;
		$this->load->model('module_model');
		$date = new DateTime();
		
		 if ( ! $this->upload->do_upload()){
			//var_dump($this->upload->display_errors());
			$mViewData['message'] = $this->upload->display_errors();
		}else{
			$data = $this->upload->data();
			//var_dump($data);
			
			if($zip->open($data['full_path'])){
				$newdir = $data['file_path'] . $data['raw_name'];
				$zip->extractTo($newdir);
				unlink($data['full_path']);
				if($config = file_get_contents($newdir . '/config.json')){
					$moduleData = json_decode($config);
					//var_dump(($config));
					//var_dump(json_decode($config));

					//File Operation
					//HTTP files
					if(file_exists($this->php_module_path.$moduleData->dir)) system("rm -rf ".$this->php_module_path.$moduleData->dir);
					rename($newdir."/web", $this->php_module_path.$moduleData->dir);
					
					//Library files
					if(file_exists($this->python_module_path.$moduleData->dir)) system("rm -rf ".$this->python_module_path.$moduleData->dir);
					rename($newdir."/python", $this->python_module_path.$moduleData->dir);

					//Script execution
					foreach ($moduleData->script as $script) {
						//echo "cd ".$this->python_module_path.$moduleData->dir." && sudo -g root ".$script;
						//system("cd ".$this->python_module_path.$moduleData->dir." && ".$script);
						shell_exec("cd ".escapeshellarg($this->python_module_path.$moduleData->dir)." && ".escapeshellcmd("sudo ".$script)." 2>&1");
					}

					//Database operation
					if(isset($moduleData->SQL)){
						$this->db->trans_start();
						foreach($moduleData->SQL as $sql){
							$this->db->query($sql);
						}
						$this->db->trans_complete();
						if ($this->db->trans_status() === FALSE){
							$this->db->display_error();
						}
					}
					
					$mViewData['message'] =  $this->module_model->newModule($moduleData);
				}else{
					$mViewData['message'] =  "Config file not found.";
				}
			}else{
				$mViewData['message'] =  "ZIP open error!";
			}
		}
		
		$this->view_model->generateView($this->load->view('successfulUpload', $mViewData, true));
	}
	
	public function module($dir = null){
		$this->load->library('table');
		$this->load->model('module_model');
		if(!isset($dir)) {
			$this->load->helper('form');
			$q = $this->db->query('SELECT * FROM libraries');
			if($q->num_rows() <= 0){
				$modules = null;
			}else{
				$modules = array();
				$result = $q->result_array();
				foreach($result as $row){
					array_push($modules, array($row['id'], $row['name'], $row['dir'], '<a class="fg-white" href="'.site_url('main/distroy/'.$row['dir']).'"><i class="icon-remove"></i></a>'));
				}
			}
			$this->view_model->generateView($this->load->view('module', array('thead'=>array('ID', 'Module Name', 'Directory', 'Remove'),'tbody'=>$modules), true));
		}else{
			if(!file_exists($this->php_module_path.$dir.'/'.$dir.'.php')){
				echo "File not exist!";
			}else{
				include($this->php_module_path.$dir.'/'.$dir.'.php');
				$module = new $dir($this->db);
				$this->view_model->generateView($this->load->view('account', $module->getAccountView($this->session->userdata('login_data')['id']), true));
			}
		}
	}
	
	public function addAccount($dir = null){
		include($this->php_module_path.$dir.'/'.$dir.'.php');
		$module = new $dir($this->db);
		$module->auth();
	}
	
	public function delAccount($dir = null, $id){
		include($this->php_module_path.$dir.'/'.$dir.'.php');
		$module = new $dir($this->db);
		$module->remv($id);
	}
	
	public function returnAdd($dir = null){
		include($this->php_module_path.$dir.'/'.$dir.'.php');
		$module = new $dir($this->db);
		$module->proc($_REQUEST, $this->session->userdata('login_data')['id']);
	}
	
	public function distroy($dir = null){
		include($this->php_module_path.$dir.'/'.$dir.'.php');
		$module = new $dir($this->db);
		$module->distroy();
		system('rm -R '.$this->php_module_path.$dir);
		redirect('main/module', 'refresh');
	}
	
	public function logout(){
		$this->load->model('login_model');
		$this->login_model->revokeToken();
		redirect(site_url(), 'refresh');
	}

	public function volume(){
		$this->load->helper('form');
		$this->load->model('volume_model');
		$this->load->model('algorithm_model');
		$drives = $this->volume_model->getVolumeList($this->session->userdata('login_data')['id']);
		$content = array();
		foreach($drives as $drive){
			include_once($this->php_module_path.$drive->dir.'/'.$drive->dir.'.php');
			$handler = new $drive->dir($this->db);
			$info = $handler->getDrivesInfo($drive->cdid);
			array_push($content, array(
				'id'=>$info['id'],
				'provider'=>$drive->name,
				'info'=>$info
			));
			unset($handler);
		}
		$codes = array();
		$algo = $this->algorithm_model->getAlgorithmList();
		$this->view_model->generateView($this->load->view('volume', array('cloudDrives'=>$content, 'algo'=>$algo), true));
		//$this->view_model->generateView($this->load->view('volume', array('cloudDrives'=>$content), true));
	}

	public function volumeAjax(){
		$this->load->model('volume_model');
		$this->load->model('algorithm_model');
		$cdid = array();
		$cd_vol = json_decode($_POST['inputSize'], true);
		foreach ($_POST['drives'] as $checked) {
			array_push($cdid, $checked);
		}

		$drives = $this->volume_model->getVolumeListByDrive($_POST['uid'], $cdid);
		$algo = $this->algorithm_model->getAlgorithmById($_POST['algo']);

		$input = array();
		foreach($drives as $drive){
			include_once($this->php_module_path.$drive->dir.'/'.$drive->dir.'.php');
			$handler = new $drive->dir($this->db);
			$info = $handler->getDrivesInfo($drive->cdid);
			if($cd_vol[$info['id']] > $info['available']){
				echo "\$cd_vol[\$info['id']]=".$cd_vol[$info['id']]." | \$info['available']=".$info['available'];
				echo "You have entered a size larger than the available size that cloud drive can allocate.<br>";
				return false;
			}
			array_push($input, array(
				'size'=>$cd_vol[$info['id']]
			));
			unset($handler);
		}

		$validate = eval($algo[0]->validateScript);
		if($validate['status'] != 0){
			echo $validate['message']."<br>";
		}else{
			echo "Algorithm: ".$algo[0]->name."<br>";
			echo "Maximum volume size: ".eval($algo[0]->sizeScript)." GB<br>";
		}
	}

	public function createVolume(){
		$this->load->model('volume_model');
		$this->load->model('algorithm_model');
		$cdid = array();
		$cd_vol = json_decode($_POST['inputSize'], true);
		foreach ($_POST['drives'] as $checked) {
			array_push($cdid, $checked);
		}

		$drives = $this->volume_model->getVolumeListByDrive($_POST['uid'], $cdid);
		$algo = $this->algorithm_model->getAlgorithmById($_POST['algo']);

		$input = array();
		foreach($drives as $drive){
			include_once($this->php_module_path.$drive->dir.'/'.$drive->dir.'.php');
			$handler = new $drive->dir($this->db);
			$info = $handler->getDrivesInfo($drive->cdid);
			if($cd_vol[$info['id']] > $info['available']){
				echo json_encode(array('status'=>1, 'message'=>"You have entered a size larger than the available size that cloud drive can allocate"));
				return false;
			}
			array_push($input, array(
				'size'=>$cd_vol[$info['id']]
			));
			unset($handler);
		}

		$validate = eval($algo[0]->validateScript);
		if($validate['status'] != 0){
			return $validate;
		}else{
			$this->volume_model->createVolume($cd_vol, $_POST['algo'], $_POST['uid'], $_POST['name'], eval($algo[0]->sizeScript));
		}
	}

}
?>
