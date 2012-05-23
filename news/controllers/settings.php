<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Copyright (c) 2011 Jakob Gillich
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are
 * met:
 * 
 * * Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above
 *   copyright notice, this list of conditions and the following disclaimer
 *   in the documentation and/or other materials provided with the
 *   distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */

class settings extends Admin_Controller {

    public function __construct() 
    {
        parent::__construct();
        $this->auth->restrict('News.Settings.Manage');
        $this->load->model('news_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('news_backend');
        Template::set('toolbar_title', lang('news_subnav_heading'));
    }
    
    /**
     * Displays an overview of all possible settings.
     * 
     * @return void
     */
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
        Template::render();
    }

    /**
     * Save settings read from POST
     * 
     * @return void 
     */
    public function save()
    {
        if ($this->input->post('submit'))
        {
            $config = array();
            $config['news_per_page'] = $this->input->post('news_per_page');
            write_config('news', $config);
            Template::set_message(lang('news_action_save_settings_success'), 'success');
            Template::redirect(SITE_AREA .'/settings/news');
        }
        else
        {
            Template::set_message(lang('news_action_save_settings_failed'), 'error');
            Template::redirect(SITE_AREA .'/settings/news');
        }
        
    }
    
    public function category_create()
    {
        if ($this->input->post('submit'))
        {
            if ($this->_save_category())
            {
                Template::set_message(lang('news_create_category_success'), 'success');
                Template::redirect(SITE_AREA .'/settings/news');
            }
        }
        Template::render();
    }
    
    /**
     * Shows the category edit page, reads category ID from POST.
     * 
     * @return void
     */
    public function category_edit()
    {
        $id = $this->input->post('category');
        if (empty($id))
        {
            $id = $this->input->post('id');
            if(empty($id))
            {
                Template::set_message(lang('news_category_invalid_id'), 'error');
                redirect(SITE_AREA .'/settings/news');
            }
        }
        
        if ($this->input->post('submit') && $this->input->post('id'))
        {
            if ($this->_save_category('update', $id))
            {
                Template::set_message(lang('news_save_category_success'), 'success');
                redirect(SITE_AREA .'/settings/news');
            }
        }
        
        Template::set('category', $this->categories_model->find($id));
        Template::render();		
    }
    
    /**
     * Deletes a category - TODO
     * 
     * @return void 
     */
    public function category_delete()
    {
        Template::render();
    }
    
    /*
     * Saves an category to db.
     * 
     * @param string $type  Insert or update?
     * @param int $id       If type is update, the category ID. 
     */
    private function _save_category($type='insert', $id=0)
    {
        $this->load->helper('slug_helper');
        $this->form_validation->set_rules('category_name','','required|max_length[255]');	
        
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
