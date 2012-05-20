<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class settings extends Admin_Controller {

    public function __construct() 
    {
        parent::__construct();
        $this->load->model('news_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('news');
        $this->auth->restrict('News.Settings.Manage');
    }
    
    
    public function index()
    {
        $config = read_config('news');
        $news_per_page_options = array();
        for($i = 1; $i != 51; $i++)
        {
            $news_per_page_options[$i] = $i;
        }
        
        Template::set('config', $config);
        Template::set('news_per_page_options', $news_per_page_options);
        Template::set('categories', $this->categories_model->order_by('id', 'asc')->find_all());
        Template::set('toolbar_title', lang('news_settings'));
        Template::render();
    }

    public function save()
    {
        if ($this->input->post('submit'))
        {
            $config = array();
            $config['news_per_page'] = $this->input->post('news_per_page');
            write_config('news', $config);
            Template::set_message(lang('news_settings_save_success'), 'success');
            Template::redirect(SITE_AREA .'/settings/news');
        }
        else
        {
            Template::set_message(lang('news_settings_save_failed'), 'error');
            Template::redirect(SITE_AREA .'/settings/news');
        }
        
    }
    
    public function category_create()
    {
        if ($this->input->post('submit'))
        {
            if ($insert_id = $this->_save_category())
            {
                    
                Template::set_message(lang('category_edit_success'), 'success');
                Template::redirect(SITE_AREA .'/settings/news');
            }
            else 
            {
                Template::set_message(lang('category_edit_failure'), 'error');
            }
        }
        Template::set('toolbar_title', lang('category_create'));
        Template::render();
    }
    
    public function category_edit()
    {
        $id = $this->input->post('category');
        if (empty($id))
        {
            $id = $this->input->post('id');
            if(empty($id))
            {
                Template::set_message(lang('category_invalid_id'), 'error');
                redirect(SITE_AREA .'/settings/news');
            }
        }
        
        if ($this->input->post('submit') && $this->input->post('category_name'))
        {
            if ($this->_save_category('update', $id))
            {
                Template::set_message('TODO_category_edit_success', 'success');
            }
            else 
            {
                Template::set_message('TODO_category_edit_failure', 'error');
            }
        }
        
        Template::set('toolbar_title', lang('category_edit'));
        Template::set('category', $this->categories_model->find($id));
        Template::render();		
    }
    
    public function category_delete()
    {
        Template::render();
    }
    
    private function _save_category($type='insert', $id=0)
    {
        $this->load->helper('slug_helper');
        $this->form_validation->set_rules('category_name','TODO_name','required|max_length[255]');	
        $this->form_validation->set_rules('category_description','TODO_description','');
        
        if ($this->form_validation->run() === FALSE)
        {
                return FALSE;
        }
        
        $data = array();
        $data['category_name'] = $this->input->post('category_name');
        $data['category_description'] = $this->input->post('category_description');
        $data['category_slug'] = slug($data['category_name'], 'news_categories', 'category_slug');
        
        if ($type == 'insert')
        {
            $id = $this->categories_model->insert($data);

            if (is_numeric($id))
            {
                    return $id;
            }
            else
            {
                    return FALSE;
            }
        }
        else if ($type == 'update')
        {
            return $this->categories_model->update($id, $data);
        }
        
        return FALSE;
    }
}
