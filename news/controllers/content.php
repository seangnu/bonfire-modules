<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class content extends Admin_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->load->model('news_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('news_backend');
        $this->load->helper('slug_helper');
        Template::set('toolbar_title', lang('news_subnav_heading'));
        Template::set_block('sub_nav', 'content/sub_nav');
    }
    
    /**
     * Display news overview
     *
     * @return void
     */
    public function index($page = 0)
    {
        $this->auth->restrict('News.Content.View');
        
        $limit = 15;
        $categories = $this->categories_model->find_all();
        $filter = $this->input->get('filter');
        
        $where = array('deleted' => 0);
        
        if($this->input->post('publish_selected'))
        {
            $this->_save_selected_news('publish', $this->input->post('checked')) ?
                Template::set_message(lang('news_publish_success'), 'success') :
                Template::set_message(lang('news_publish_failure') . $this->news_model->error, 'error');
            
        }
        elseif($this->input->post('unpublish_selected'))
        {
            $this->_save_selected_news('unpublish', $this->input->post('checked')) ?
                Template::set_message(lang('news_unpublish_success'), 'success') :
                Template::set_message(lang('news_unpublish_failure') . $this->news_model->error, 'error');
        }
        elseif($this->input->post('delete_selected'))
        {
            $this->_save_selected_news('delete', $this->input->post('checked')) ?
                Template::set_message(lang('news_delete_success'), 'success') :
                Template::set_message(lang('news_delete_failure') . $this->news_model->error, 'error');
        }
        elseif($this->input->post('purge_selected'))
        {
            $this->_save_selected_news('purge', $this->input->post('checked')) ?
                Template::set_message(lang('news_purge_success'), 'success') :
                Template::set_message(lang('news_purge_failure') . $this->news_model->error, 'error');
        }
        elseif($this->input->post('restore_selected'))
        {
            $this->_save_selected_news('restore', $this->input->post('checked')) ?
                Template::set_message(lang('news_restore_success'), 'success') :
                Template::set_message(lang('news_restore_failure') . $this->news_model->error, 'error');
        }

        switch($filter)
        {
            case 'published':
                $where['news_published'] = 1;
                break;
            case 'unpublished':
                $where['news_published'] = 0;
                break;
            case 'deleted':
                $where['deleted'] = 1;
                break;
            case 'category':
                $category_id = (int)$this->input->get('category_id');
                $where['category_id'] = $category_id;

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
        
        
        $search_term = $this->input->post('search_term');
        if($search_term)
        {
            $this->news_model->likes('news_title', $search_term);
            $likes = TRUE;
            $likes_field = 'news_title';
            $likes_value = $search_term;
            Template::set('search_term', $search_term);
        }
        else
        {
            $likes = FALSE;
            $this->news_model->limit($limit, $page);
        }
        $this->news_model->where($where)->select('id, deleted, news_title, news_slug, category_id, news_published, created_on');
        $news = $this->news_model->order_by('id', 'desc')->find_all(FALSE);
        Template::set('news', $news);
        
        if($likes)
        {
            $count = $this->news_model->where($where)->select('id')->likes($likes_field, $likes_value)->count_all();
        }
        else
        {
            $count = $this->news_model->where($where)->select('id')->count_all();
        }
        
        
        $this->load->library('pagination');
	$this->pager['base_url'] = site_url(SITE_AREA .'/content/news/index');
        $this->pager['total_rows'] = $count;
        $this->pager['per_page'] = $limit;
        $this->pager['uri_segment'] = 5;
        $this->pagination->initialize($this->pager);
        
        Template::set('categories', $categories);
        Template::set('filter', $filter);
        Template::set('current_url', current_url());
        
        Template::render();
    }

    /**
     * Create a news
     *
     * @return void
     */
    public function create()
    {       
        $this->auth->restrict('News.Content.Edit');
                
        if ($this->input->post('save') || $this->input->post('publish'))
        {
            if($this->input->post('publish'))
            {
                $insert_id = $this->_save_news('insert', 0, array('news_published' => 1));
            }
            else
            {
                $insert_id = $this->_save_news();
            }
            if ($insert_id)
            {

                Template::set_message(lang("news_create_success"), 'success');
                Template::redirect(SITE_AREA .'/content/news');
            }
        }
        Template::set('categories', $this->categories_model->order_by('id', 'asc')->find_all());
        Assets::add_js(Template::theme_url('js/editors/ckeditor/ckeditor.js'));
        Assets::add_js($this->load->view('content/js', null, true), 'inline');
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
            Template::set_message('news_invalid_id', 'error');
            redirect(SITE_AREA .'/content/news');
        }

        if ($this->input->post('save') || $this->input->post('publish'))
        {
            if($this->input->post('publish'))
            {
                $return = $this->_save_news('update', $id, array('news_published' => 1));
            }
            else
            {
                $return = $this->_save_news('update', $id);
            }
            
            if ($return)
            {
                Template::set_message(lang('news_edit_success'), 'success');
            }
        }
        Template::set('news', $this->news_model->find($id));
        Template::set('categories', $this->categories_model->order_by('id', 'asc')->find_all());
        
        Assets::add_js(Template::theme_url('js/editors/ckeditor/ckeditor.js'));
        Assets::add_js($this->load->view('content/js', null, true), 'inline');
        Template::render();
    }

    /**
     * Delete a news
     *
     * @return void
     */
    public function delete($id = FALSE)
    {
        $this->auth->restrict('News.Content.Edit');

        if ( ! empty($id) && $id)
        {	
            if ($this->news_model->delete($id))
            {
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
    private function _save_news($type='insert', $id = 0, $data = NULL)
    {
        $this->form_validation->set_rules('news_title','Title','required|max_length[255]');
        $this->form_validation->set_rules('category','Category','required|integer|max_length[11]');
        
        if ($this->form_validation->run() === FALSE)
        {
            return FALSE;
        }

        $data['news_title']       = $this->input->post('news_title');

        if($this->input->post('news_slug') == "")
        {
            $data['news_slug']        = slug($this->input->post('news_title'), 'news', 'news_slug');
        }
        else
        {
            $data['news_slug']        = slug($this->input->post('news_slug'), 'news', 'news_slug');
        }
     
        $data['news_text']        = $this->input->post('news_text');
        $data['category_id']      = $this->input->post('category');
        $data['deleted']          = 0;

        if($this->input->post('news_published'))
        {
            $data['news_published'] = 1;
        }
        if( ! isset($data['news_published']))
        {
            $data['news_published'] = 0;
        }

        if ($type == 'insert')
        {
            $id = $this->news_model->insert($data);

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
            return $this->news_model->update($id, $data);
        }

        return FALSE;
    }
    
    /*
     * Save selected changes to database
     * 
     * @param string $type  Possible: publish, unpublish, delete, purge
     * @param array $ids    The News Ids.
     * @return bool
     */
    private function _save_selected_news($type, $ids)
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
                $data['news_published'] = 1;
                $this->news_model->update($i, $data) ? '' : $return = FALSE;
            }
            return $return;
        }
        elseif($type == 'unpublish')
        {
            foreach($ids as $i)
            {
                $data['news_published'] = 0;
                $this->news_model->update($i, $data) ? '' : $return = FALSE;
            }
            return $return;
        }
        elseif($type == 'delete')
        {
            foreach($ids as $i)
            {
                $this->news_model->delete($i) ? '' : $return = FALSE;
            }
            return $return;
        }
        elseif($type == 'purge')
        {
            $this->auth->restrict('News.Content.Delete');
            foreach($ids as $i)
            {
                $this->news_model->set_soft_deletes(FALSE);
                $this->news_model->delete($i) ? '' : $return = FALSE;
            }
            return $return;
        }
        elseif($type == 'restore')
        {
            foreach($ids as $i)
            {
                $data['deleted'] = 0;
                $this->news_model->update($i, $data) ? '' : $return = FALSE;
            }
            return $return;
        }
        return FALSE;
    }
}
