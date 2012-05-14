<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class content extends Admin_Controller {

	public function __construct() 
	{
		parent::__construct();

		$this->auth->restrict('Pages.Content.View');
		$this->load->model('pages_model', null, true);
        $this->load->model('categories_model', null, true);
		$this->lang->load('pages');
        $this->load->helper('ui/ui_helper');
        Template::set('toolbar_title', lang('pages_manage'));
		
		
	}
	
	public function index() 
	{
		Assets::add_js($this->load->view('content/js', null, true), 'inline');
		
		Template::set('records', $this->pages_model->find_all());
        Template::set('categories', $this->categories_model->find_all());
		Template::render();
	}
	
	public function create() 
	{
		$this->auth->restrict('Pages.Content.Create');

		if ($this->input->post('submit'))
		{
			if ($insert_id = $this->save_pages())
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('pages_act_create_record').': ' . $insert_id . ' : ' . $this->input->ip_address(), 'pages');
					
				Template::set_message(lang("pages_create_success"), 'success');
				Template::redirect(SITE_AREA .'/content/pages');
			}
			else 
			{
				Template::set_message(lang('pages_create_failure') . $this->pages_model->error, 'error');
			}
		}
	

		Template::render();
	}
	
	public function edit() 
	{
		$this->auth->restrict('Pages.Content.Edit');

		$id = (int)$this->uri->segment(5);
		
		if (empty($id))
		{
			Template::set_message(lang('pages_invalid_id'), 'error');
			redirect(SITE_AREA .'/content/pages');
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
	
		Template::render();		
	}
	
	public function delete() 
	{	
		$this->auth->restrict('Pages.Content.Delete');

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
		
		redirect(SITE_AREA .'/content/pages');
	}
	

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
        if($this->input->post('pages_slug') == "")
            $data['pages_slug']        = $this->_mkslug($this->input->post('pages_title'));
        else
		    $data['pages_slug']        = $this->_mkslug($this->input->post('pages_slug'));
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

    /**
     * Takes an string and returns a slug, useable for URLs
     *
     * @param string
     * @return string the slug
     */
    private function _mkslug($string)
    {
        $slug = url_title($string);
        $slug = strtolower($slug);
        $i = 0;
        $params = array ();
        $params['pages_slug'] = $slug;
        if ($this->input->post('id'))
        {
            $params['id !='] = $this->input->post('id');
        }
        
        while ($this->db->where($params)->get('pages')->num_rows())
        {
            if (!preg_match ('/-{1}[0-9]+$/', $slug )) {
                $slug .= '-' . ++$i;
            } else {
                $slug = preg_replace ('/[0-9]+$/', ++$i, $slug );
            }
            $params ['pages_slug'] = $slug;
            }
        return $slug;
    }
}
