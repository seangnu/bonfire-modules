<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class settings extends Admin_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->auth->restrict('Pages.Settings.View');
        $this->load->model('pages_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('pages');
    }

    public function index() 
    {
        Template::set('categories', $this->categories_model->order_by('id', 'asc')->find_all());

        Template::render();
    }

    public function category_create()
    {
        //TODO $this->auth->restrict('Pages.Settings.Categories');
        
        if ($this->input->post('submit'))
        {
            if ($insert_id = $this->_save_category())
            {
                    
                Template::set_message(lang('category_create_success'), 'success');
                Template::redirect(SITE_AREA .'/settings/pages');
            }
            else 
            {
                Template::set_message(lang('pages_create_failure') . $this->news_model->error, 'error');
            }
        }
        
        Template::render();
    }
    
    public function category_edit()
    {
        //TODO $this->auth->restrict('Pages.Settings.Categories');
        $id = $this->input->post('category');
        if (empty($id))
        {
            $id = $this->input->post('id');
            if(empty($id))
            {
                Template::set_message(lang('pages_invalid_category_id'), 'error');
                redirect(SITE_AREA .'/settings/pages');
            }
        }
        
        if ($this->input->post('submit') && $this->input->post('category_name'))
        {
            if ($this->_save_category('update', $id))
            {
                    Template::set_message(lang('category_edit_success'), 'success');
            }
            else 
            {
                    Template::set_message(lang('category_edit_failure') . $this->pages_model->error, 'error');
            }
        }
        
        
        Template::set('category', $this->categories_model->find($id));
        Template::set('categories', $this->categories_model->find_all());
        Template::render();		
    }
    
    public function category_delete()
    {
        //TODO $this->auth->restrict('Pages.Settings.Categories');
        Template::render();
    }
    
    private function _save_category($type='insert', $id=0)
    {
        $this->form_validation->set_rules('category_name', lang('category_name'), 'required|max_length[255]');	
        $this->form_validation->set_rules('category_description', lang('category_description'), '');
        
        if ($this->form_validation->run() === FALSE)
        {
                return FALSE;
        }
        
        $data = array();
        $data['category_name'] = $this->input->post('category_name');
        $data['category_description'] = $this->input->post('category_description');
        
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
