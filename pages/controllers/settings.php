<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class settings extends Admin_Controller {

	public function __construct() 
	{
		parent::__construct();

		$this->auth->restrict('Pages.Settings.View');
		$this->load->model('pages_model', null, true);
		$this->lang->load('pages');
		
		
	}
	
	public function index() 
	{
		Assets::add_js($this->load->view('settings/js', null, true), 'inline');
		
		Template::render();
	}


	public function create() 
	{
		$this->auth->restrict('Pages.Settings.Create');

	}
	

	public function edit() 
	{
		$this->auth->restrict('Pages.Settings.Edit');
	}
	

	public function delete() 
	{	
		$this->auth->restrict('Pages.Settings.Delete');
	}
	

}
