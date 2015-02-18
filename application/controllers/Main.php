<?php defined('BASEPATH') OR exit('No direct script access allowed');



class Main extends CI_Controller {

	public function __construct(){
		parent::__construct();
		session_start();
		$this->load->model('login_model');
		if(!$this->login_model->isAuthenticated()) return redirect('login/index', 'location');
		$this->login_model->refreshToken($this->session->userdata('login_data'));
	}

	public function index(){
		$this->load->model('module_model');
		$data['modules'] = $this->module_model->getModuleList();
		$this->generateView($this->load->view('content', $data, true));
	}
	
	protected function generateView($contentData = null){
		$this->load->model('profile_model');
	
		$data = array();
		$navData = array('menus'=>array());
		$navData['profile'] = $this->profile_model->getProfile($this->session->userdata('login_data'));
		
		$this->load->model('module_model');
		$settings = array('title'=>'Setting', 'link'=>'#', 'submenu'=>array(), 'dividerBefore'=>false);
		
		foreach($this->module_model->getModuleList() as $module){
			array_push($settings['submenu'], array('title'=>$module['name'], 'link'=>site_url('main/module/'.$module['dir']), 'dividerBefore'=>false));
		}
		array_push($settings['submenu'], array('title'=>'Modules', 'link'=>site_url('main/module'), 'dividerBefore'=>true));
		array_push($navData['menus'], $settings);
		
		$data['nav'] = $this->load->view('nav', $navData, true);
		$data['content'] = $contentData;
		$data['morrisChart'] = false;
		$this->load->view('main', $data);
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
					if(file_exists("./application/third_party/".$moduleData->dir)) system("rm -R ./application/third_party/".$moduleData->dir);
					rename($newdir.'/'.$moduleData->dir, "./application/third_party/".$moduleData->dir);
					
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
		
		$this->generateView($this->load->view('successfulUpload', $mViewData, true));
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
			$this->generateView($this->load->view('module', array('thead'=>array('ID', 'Module Name', 'Directory', 'Remove'),'tbody'=>$modules), true));
		}else{
			if(!file_exists(APPPATH.'third_party/'.$dir.'/'.$dir.'.php')){
				echo "File not exist!";
			}else{
				include(APPPATH.'third_party/'.$dir.'/'.$dir.'.php');
				$module = new $dir($this->db);
				$this->generateView($this->load->view('account', $module->getAccountView(), true));
			}
		}
	}
	
	public function addAccount($dir = null){
		include(APPPATH.'third_party/'.$dir.'/'.$dir.'.php');
		$module = new $dir($this->db);
		$module->auth();
	}
	
	public function delAccount($dir = null, $id){
		include(APPPATH.'third_party/'.$dir.'/'.$dir.'.php');
		$module = new $dir($this->db);
		$module->remv($id);
	}
	
	public function returnAdd($dir = null){
		include(APPPATH.'third_party/'.$dir.'/'.$dir.'.php');
		$module = new $dir($this->db);
		$module->proc($_REQUEST);
	}
	
	public function distroy($dir = null){
		include(APPPATH.'third_party/'.$dir.'/'.$dir.'.php');
		$module = new $dir($this->db);
		$module->distroy();
		system('rm -R '.APPPATH.'third_party/'.$dir);
		redirect('main/module', 'refresh');
	}
	
	public function logout(){
		$this->load->model('login_model');
		$this->login_model->revokeToken();
		redirect(site_url(), 'refresh');
	}

}
?>