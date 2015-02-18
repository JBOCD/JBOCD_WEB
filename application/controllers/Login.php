<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function index(){
		$this->load->model('login_model');
		if($id = $this->login_model->isAuthenticated()){
			$this->login_model->refreshToken($id);
			return redirect('main', 'location');
		}
		
		$this->load->helper('form');
		$data = array();
		if($this->session->userdata('login_error')){
			$data['error'] = $this->session->userdata('login_error');
			$this->session->unset_userdata('login_error');
		}
		
		$this->load->view('login', $data);
	}
	
	public function authenticate(){
		$this->load->model('login_model');
		$this->login_model->authenticate($_POST['login'], $_POST['pw']);
		$this->login_model->isAuthenticated();
		$this->index();
	}
	
}

?>