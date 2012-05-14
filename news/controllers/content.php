<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class content extends Admin_Controller {

	public function __construct() 
	{
		parent::__construct();

		$this->auth->restrict('News.Content.View');
		$this->load->model('news_model', null, true);
        $this->load->model('categories_model', null, true);
		$this->lang->load('news');
        $this->load->helper('ui/ui_helper');
        Template::set('toolbar_title', lang('news_manage'));
	}
    /**
     * Display all news
     *
     * @return void
     */
	public function index() 
	{
		Assets::add_js($this->load->view('content/js', null, true), 'inline');

		Template::set('records', $this->news_model->order_by('created_on', 'desc')->find_all());
        Template::set('categories', $this->categories_model->order_by('category_name', 'asc')->find_all());
		Template::render();
	}
	
    /**
     * Create a news
     *
     * @return void
     */
	public function create() 
	{
		$this->auth->restrict('News.Content.Create');

		if ($this->input->post('submit'))
		{
			if ($insert_id = $this->save_news())
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('news_act_create_record').': ' . $insert_id . ' : ' . $this->input->ip_address(), 'news');
					
				Template::set_message(lang("news_create_success"), 'success');
				Template::redirect(SITE_AREA .'/content/news');
			}
			else 
			{
				Template::set_message(lang('news_create_failure') . $this->news_model->error, 'error');
			}
		}
	
		Template::set('toolbar_title', lang('news_create_new_button'));
		Template::set('toolbar_title', lang('news_create') . ' news');
		Template::render();
	}
	
    /**
     * Edit a news
     *
     * @return void
     */
	public function edit() 
	{
		$this->auth->restrict('News.Content.Edit');

		$id = (int)$this->uri->segment(5);
		
		if (empty($id))
		{
			Template::set_message(lang('news_invalid_id'), 'error');
			redirect(SITE_AREA .'/content/news');
		}
	
		if ($this->input->post('submit'))
		{
			if ($this->save_news('update', $id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('news_act_edit_record').': ' . $id . ' : ' . $this->input->ip_address(), 'news');
					
				Template::set_message(lang('news_edit_success'), 'success');
			}
			else 
			{
				Template::set_message(lang('news_edit_failure') . $this->news_model->error, 'error');
			}
		}
		
		Template::set('news', $this->news_model->find($id));
	
		Template::set('toolbar_title', lang('news_edit_heading'));
		Template::set('toolbar_title', lang('news_edit') . ' news');
		Template::render();		
	}
	
    /**
     * Delete a news
     *
     * @return void
     */
	public function delete() 
	{	
		$this->auth->restrict('News.Content.Delete');

		$id = $this->uri->segment(5);
	
		if (!empty($id))
		{	
			if ($this->news_model->delete($id))
			{
				// Log the activity
				$this->activity_model->log_activity($this->auth->user_id(), lang('news_act_delete_record').': ' . $id . ' : ' . $this->input->ip_address(), 'news');
					
				Template::set_message(lang('news_delete_success'), 'success');
			} else
			{
				Template::set_message(lang('news_delete_failure') . $this->news_model->error, 'error');
			}
		}
		
		redirect(SITE_AREA .'/content/news');
	}

    /**
     * Save news to database
     *
     * @return bool
     */
	private function save_news($type='insert', $id=0) 
	{	
					
		$this->form_validation->set_rules('news_title','Title','required|max_length[255]');			
		$this->form_validation->set_rules('news_slug','Slug','max_length[255]');			
		$this->form_validation->set_rules('news_text','Text','');

		if ($this->form_validation->run() === FALSE)
		{
			return FALSE;
		}
		
		// make sure we only pass in the fields we want
		
		$data = array();
		$data['news_title']       = $this->input->post('news_title');

        if($this->input->post('news_slug') == "")
            $data['news_slug']        = $this->_mkslug($this->input->post('news_title'));
        else
		    $data['news_slug']        = $this->_mkslug($this->input->post('news_slug'));

		$data['news_text']        = $this->input->post('news_text');
		
		if ($type == 'insert')
		{
			$id = $this->news_model->insert($data);
			
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
			$return = $this->news_model->update($id, $data);
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
        $params['news_slug'] = $slug;
        if ($this->input->post('id')) {
            $params['id !='] = $this->input->post('id');
        }
        
        while ($this->db->where($params)->get('news')->num_rows()) {
            if (!preg_match ('/-{1}[0-9]+$/', $slug )) {
                $slug .= '-' . ++$i;
            } else {
                $slug = preg_replace ('/[0-9]+$/', ++$i, $slug );
            }
            $params ['news_slug'] = $slug;
            }
        return $slug;
    }


}
