<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class content extends Admin_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->auth->restrict('Files.Content.View');
        $this->load->model('files_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('files');
        Template::set('toolbar_title', lang('files_manage'));
        Template::set_block('sub_nav', 'content/sub_nav');

    }

    public function index($page = 0) 
    {
        $limit = 15;
        $categories = $this->categories_model->find_all();
        $filter = $this->input->get('filter');
                
        if($this->input->post('publish_selected'))
        {
            $this->_save_selected_files('publish', $this->input->post('checked')) ?
                Template::set_message(lang('files_edit_success'), 'success') :
                Template::set_message(lang('files_edit_failure') . $this->files_model->error, 'error');
            
        }
        elseif($this->input->post('unpublish_selected'))
        {
            $this->_save_selected_files('unpublish', $this->input->post('checked')) ?
                Template::set_message(lang('files_edit_success'), 'success') :
                Template::set_message(lang('files_edit_failure') . $this->files_model->error, 'error');
        }
        
        $where = array();
        switch($filter)
        {
            case 'published':
                $where['files.file_published'] = 1;
                break;
            case 'unpublished':
                $where['files.file_published'] = 0;
                break;
            case 'category':
                $category_id = (int)$this->input->get('category_id');
                $where['files.category_id'] = $category_id;

                foreach ($categories as $c)
                {
                    if ($c->id == $category_id)
                    {
                        Template::set('filter_category', $c->category_name);
                        break;
                    }
                }
                break;
            default:
                break;
        }
        
        Template::set('filter', $filter);
        Template::set('current_url', current_url());
        
        $search_term = $this->input->post('search_term');
        if($search_term)
        {
            $this->files_model->likes('file_title', $search_term);
            //$this->news_model->likes('news_text', $search_term);
            Template::set('search_term', $search_term);
        }
        else
        {
            $this->files_model->limit($limit, $page);
        }
        $this->files_model->where($where)->select('files.id, file_title, file_name, category_id, file_published, created_on');
        $files = $this->files_model->order_by('id', 'desc')->find_all(FALSE);
        Template::set('files', $files);
        $this->load->library('pagination');
	$this->pager['base_url'] = site_url(SITE_AREA .'/content/files/index');
        $this->pager['total_rows'] = count($files);
        $this->pager['per_page'] = $limit;
        $this->pager['uri_segment'] = 5;
        $this->pagination->initialize($this->pager);
        
        Template::set('categories', $categories);
        
        Template::render();
    }

    public function create() 
    {
            if ($this->input->post('submit'))
            {
                    if ($insert_id = $this->_save_file())
                    {
                            Template::set_message(lang("file_create_success"), 'success');
                            Template::redirect(SITE_AREA .'/content/files');
                    }
                    else 
                    {
                            Template::set_message(lang('file_create_failure') . $this->files_model->error, 'error');
                    }
            }

            Template::set('categories', $this->categories_model->find_all());
            Template::render();
    }

    public function edit() 
    {
            $this->auth->restrict('Files.Content.Edit');

            $id = (int)$this->uri->segment(5);

            if (empty($id))
            {
                    Template::set_message(lang('file_invalid_id'), 'error');
                    redirect(SITE_AREA .'/content/files');
            }

            if ($this->input->post('submit'))
            {
                    if ($this->_save_file('update', $id))
                    {
                            // Log the activity
                            //$this->activity_model->log_activity($this->auth->user_id(), lang('file_act_edit_record').': ' . $id . ' : ' . $this->input->ip_address(), 'files');

                            Template::set_message(lang('file_edit_success'), 'success');
                    }
                    else 
                    {
                            Template::set_message(lang('file_edit_failure') . $this->files_model->error, 'error');
                    }
            }

            Template::set('file', $this->files_model->find($id));
            Template::set('categories', $this->categories_model->find_all());
            Template::render();		
    }

    public function delete() 
    {	
            $this->auth->restrict('Files.Content.Delete');

            $id = $this->uri->segment(5);

            if (!empty($id))
            {	
                    if ($this->files_model->delete($id))
                    {
                            // Log the activity
                            $this->activity_model->log_activity($this->auth->user_id(), lang('file_act_delete_record').': ' . $id . ' : ' . $this->input->ip_address(), 'files');

                            Template::set_message(lang('file_delete_success'), 'success');
                    } else
                    {
                            Template::set_message(lang('file_delete_failure') . $this->files_model->error, 'error');
                    }
            }

            redirect(SITE_AREA .'/content/files');
    }

    private function _save_file($type='insert', $id=0) 
    {	

        $this->form_validation->set_rules('file_title','Name','required|max_length[255]');			
        $this->form_validation->set_rules('file_description','Description','');			
        $this->form_validation->set_rules('category_id','Category','max_length[11]');

        if ($this->form_validation->run() === FALSE)
        {
            return FALSE;
        }

        // make sure we only pass in the fields we want

        $data = array('file_published' => 0);
        $data['file_title']        = $this->input->post('file_title');
        $data['file_description']        = $this->input->post('file_description');
        $data['category_id']        = $this->input->post('file_category_id');

        if ($type == 'insert')
        {
            $upload_config['upload_path'] = './files/';
            $upload_config['allowed_types'] = '*';

            $this->load->library('upload', $upload_config);
        
            if( ! $this->upload->do_upload())
            {
                return FALSE;
            }
            $uploaded = $this->upload->data();
            
            $data['file_name'] = $uploaded['file_name'];
            $data['is_image'] = $uploaded['is_image'];
            $data['file_size'] = $uploaded['file_size'];
        
            $id = $this->files_model->insert($data);

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
            $return = $this->files_model->update($id, $data);
        }

        return $return;
    }
    
    private function _save_selected_files($type, $ids)
    {
        if(empty($ids))
        {
            return TRUE;
        }
        
        $return = TRUE;
        if($type == 'publish')
        {
            foreach($ids as $i)
            {
                $data['file_published'] = 1;
                $this->files_model->update($i, $data) ? '' : $return = FALSE;
            }
            return $return;
        }
        elseif($type == 'unpublish')
        {
            foreach($ids as $i)
            {
                $data['file_published'] = 0;
                $this->files_model->update($i, $data) ? '' : $return = FALSE;
            }
            return $return;
        }
    }

}