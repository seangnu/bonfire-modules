<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class settings extends Admin_Controller {
    
    public function __construct() 
    {
        parent::__construct();

        //$this->auth->restrict('Files.Settings.View');
        $this->load->model('files_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('files');
        $this->load->helper('slug');
    }

    public function index() 
    {
        $config = read_config('files');
        $files_per_page_options = array();
        for($i = 1; $i != 101; $i++)
        {
            $files_per_page_options[$i] = $i;
        }
        
        Template::set('config', $config);
        Template::set('files_per_page_options', $files_per_page_options);
        Template::set('categories', $this->categories_model->order_by('id', 'asc')->find_all());
        Template::render();
    }
    
    public function save()
    {
        if ($this->input->post('submit'))
        {
            $config = array();
            $config['files_per_page'] = $this->input->post('files_per_page');
            write_config('files', $config);
            
            Template::set_message('TODO_successfull', 'success');
            Template::redirect(SITE_AREA .'/settings/files');
        }
        else
        {
            Template::set_message('TODO_failed', 'error');
            Template::redirect(SITE_AREA .'/settings/files');
        }
        
    }
    
    public function category_create()
    {
        
        if ($this->input->post('submit'))
        {
            if ($insert_id = $this->_save_category())
            {
                    
                    Template::set_message('TODO_successfull created', 'success');
                    Template::redirect(SITE_AREA .'/settings/files');
            }
            else 
            {
                    Template::set_message('TODO_creation failed' . $this->files_model->error, 'error');
            }
        }
        
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
                Template::set_message('TODO_category_invalid_id', 'error');
                redirect(SITE_AREA .'/settings/files');
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
                    Template::set_message('TODO_category_edit_failure' . $this->files_model->error, 'error');
            }
        }
        
        
        Template::set('category', $this->files_model->find($id));
        Template::render();		
    }
    
    public function category_delete()
    {
        //TODO
        Template::render();
    }
    
    private function _save_category($type='insert', $id=0)
    {
        $this->form_validation->set_rules('category_name', 'TODO_name', 'required|max_length[255]');	
        $this->form_validation->set_rules('category_description', 'TODO_description', '');
        
        if ($this->form_validation->run() === FALSE)
        {
            return FALSE;
        }
        
        $data = array();
        $data['category_name'] = $this->input->post('category_name');
        $data['category_description'] = $this->input->post('category_description');
        $data['category_slug'] = slug($data['category_name'], 'files_categories', 'category_slug');
        
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