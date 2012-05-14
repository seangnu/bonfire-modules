<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class reports extends Admin_Controller {

	//--------------------------------------------------------------------

	public function __construct() 
	{
		parent::__construct();

		$this->auth->restrict('Pages.Reports.View');
		$this->load->model('pages_model', null, true);
		$this->lang->load('pages');
		
		
	}
	
	//--------------------------------------------------------------------

	/*
		Method: index()
		
		Displays a list of form data.
	*/
	public function index() 
	{
		Assets::add_js($this->load->view('reports/js', null, true), 'inline');
		
		Template::set('records', $this->pages_model->find_all());
		Template::set('toolbar_title', "Manage pages");
		Template::render();
	}
	
	//--------------------------------------------------------------------

	/*
		Method: create()
		
		Creates a pages object.
	*/
	public function create() 
	{
		$this->auth->restrict('Pages.Reports.Create');

		if ($this->input->post('submit'))
		{
			if ($insert_id = $this->save_pages())
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('pages_act_create_record').': ' . $insert_id . ' : ' . $this->input->ip_address(), 'pages');
					
				Template::set_message(lang("pages_create_success"), 'success');
				Template::redirect(SITE_AREA .'/reports/pages');
			}
			else 
			{
				Template::set_message(lang('pages_create_failure') . $this->pages_model->error, 'error');
			}
		}
	
		Template::set('toolbar_title', lang('pages_create_new_button'));
		Template::set('toolbar_title', lang('pages_create') . ' pages');
		Template::render();
	}
	
	//--------------------------------------------------------------------

	/*
		Method: edit()
		
		Allows editing of pages data.
	*/
	public function edit() 
	{
		$this->auth->restrict('Pages.Reports.Edit');

		$id = (int)$this->uri->segment(5);
		
		if (empty($id))
		{
			Template::set_message(lang('pages_invalid_id'), 'error');
			redirect(SITE_AREA .'/reports/pages');
		}
	
		if ($this->input->post('submit'))
		{
			if ($this->save_pages('update', $id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('pages_act_edit_record').': ' . $id . ' : ' . $this->input->ip_address(), 'pages');
					
				Template::set_message(lang('pages_edit_success'), 'success');
			}
			else 
			{
				Template::set_message(lang('pages_edit_failure') . $this->pages_model->error, 'error');
			}
		}
		
		Template::set('pages', $this->pages_model->find($id));
	
		Template::set('toolbar_title', lang('pages_edit_heading'));
		Template::set('toolbar_title', lang('pages_edit') . ' pages');
		Template::render();		
	}
	
	//--------------------------------------------------------------------

	/*
		Method: delete()
		
		Allows deleting of pages data.
	*/
	public function delete() 
	{	
		$this->auth->restrict('Pages.Reports.Delete');

		$id = $this->uri->segment(5);
	
		if (!empty($id))
		{	
			if ($this->pages_model->delete($id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('pages_act_delete_record').': ' . $id . ' : ' . $this->input->ip_address(), 'pages');
					
				Template::set_message(lang('pages_delete_success'), 'success');
			} else
			{
				Template::set_message(lang('pages_delete_failure') . $this->pages_model->error, 'error');
			}
		}
		
		redirect(SITE_AREA .'/reports/pages');
	}
	
	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// !PRIVATE METHODS
	//--------------------------------------------------------------------
	
	/*
		Method: save_pages()
		
		Does the actual validation and saving of form data.
		
		Parameters:
			$type	- Either "insert" or "update"
			$id		- The ID of the record to update. Not needed for inserts.
		
		Returns:
			An INT id for successful inserts. If updating, returns TRUE on success.
			Otherwise, returns FALSE.
	*/
	private function save_pages($type='insert', $id=0) 
	{	
					
		$this->form_validation->set_rules('pages_title','Title','max_length[255]');			
		$this->form_validation->set_rules('pages_slug','Slug','max_length[255]');			
		$this->form_validation->set_rules('pages_text','Text','');

		if ($this->form_validation->run() === FALSE)
		{
			return FALSE;
		}
		
		// make sure we only pass in the fields we want
		
		$data = array();
		$data['pages_title']        = $this->input->post('pages_title');
		$data['pages_slug']        = $this->input->post('pages_slug');
		$data['pages_text']        = $this->input->post('pages_text');
		
		if ($type == 'insert')
		{
			$id = $this->pages_model->insert($data);
			
			if (is_numeric($id))
			{
				$return = $id;
			} else
			{
				$return = FALSE;
			}
		}
		else if ($type == 'update')
		{
			$return = $this->pages_model->update($id, $data);
		}
		
		return $return;
	}

	//--------------------------------------------------------------------



}