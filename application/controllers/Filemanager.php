<?php defined('BASEPATH') OR exit('No direct script access allowed');



class Filemanager extends CI_Controller {

	public function __construct(){
		parent::__construct();
		session_start();
		$this->load->model('view_model');
		$this->load->model('login_model');
		if(!$this->login_model->isAuthenticated()) return redirect('login/index', 'location');
		$this->login_model->refreshToken($this->session->userdata('login_data'));
	}

	public function index($ldid){
		$this->view_model->generateView($this->load->view('filemanager', array('CSRF'=>$this->login_model->getCSRF(), 'ldid'=>$ldid, 'uid'=>$this->session->userdata('login_data')['id']), true));
	}
}