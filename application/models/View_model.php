<?php defined('BASEPATH') OR exit('No direct script access allowed');

ini_set('display_errors', 'On');
error_reporting(E_ALL);

class View_model extends CI_Model {

	public function __construct(){
		parent::__construct();

	}
	
	public function generateView($contentData = null){
		$this->load->model('profile_model');
		$this->load->model('volume_model');
	
		$data = array();
		$navData = array('menus'=>array());
		$navData['profile'] = $this->profile_model->getProfile($this->session->userdata('login_data')['id']);
		
		$this->load->model('module_model');
		$settings = array('title'=>'Setting', 'link'=>'#', 'submenu'=>array(), 'dividerBefore'=>false);
		
		foreach($this->module_model->getModuleList() as $module){
			array_push($settings['submenu'], array('title'=>$module['name'], 'link'=>site_url('main/module/'.$module['dir']), 'dividerBefore'=>false));
		}
		array_push($settings['submenu'], array('title'=>'Modules', 'link'=>site_url('main/module'), 'dividerBefore'=>true));
		array_push($settings['submenu'], array('title'=>'Volumes', 'link'=>site_url('main/volume'), 'dividerBefore'=>true));
		array_push($navData['menus'], $settings);

		$logicalVol = $this->volume_model->getLogicalVolumeList($this->session->userdata('login_data')['id']);
		if(sizeof($logicalVol) > 0){
			$settings = array('title'=>'File Manager', 'link'=>'#', 'submenu'=>array(), 'dividerBefore'=>false);
			foreach ($logicalVol as $ld) {
				array_push($settings['submenu'], array('title'=>$ld->name, 'link'=>site_url('filemanager/index/'.$ld->ldid), 'dividerBefore'=>false));
			}
			array_push($navData['menus'], $settings);
		}

		
		
		$data['nav'] = $this->load->view('nav', $navData, true);
		$data['content'] = $contentData;
		$data['morrisChart'] = false;
		$this->load->view('main', $data);
	}

}