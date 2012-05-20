<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class content extends Admin_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->auth->restrict('News.Content.View');
        $this->load->model('news_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('news');
        $this->load->helper('slug_helper');
        Template::set('toolbar_title', lang('news_manage'));
        Template::set_block('sub_nav', 'content/sub_nav');
    }
    
    /**
     * Display news overview
     *
     * @return void
     */
    public function index($page = 0)
    {
        $limit = 15;
        $categories = $this->categories_model->find_all();
        $filter = $this->input->get('filter');
        
        $where = array('news.deleted' => 0);
        $show_deleted = FALSE;
        
        if($this->input->post('publish_selected'))
        {
            $this->_save_selected_news('publish', $this->input->post('checked')) ?
                Template::set_message(lang('news_edit_success'), 'success') :
                Template::set_message(lang('news_edit_failure') . $this->news_model->error, 'error');
            
        }
        elseif($this->input->post('unpublish_selected'))
        {
            $this->_save_selected_news('unpublish', $this->input->post('checked')) ?
                Template::set_message(lang('news_edit_success'), 'success') :
                Template::set_message(lang('news_edit_failure') . $this->news_model->error, 'error');
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
                Template::set_message(lang('news_delete_success'), 'success') :
                Template::set_message(lang('news_delete_failure') . $this->news_model->error, 'error');
        }
        elseif($this->input->post('restore_selected'))
        {
            $this->_save_selected_news('restore', $this->input->post('checked')); /* ?
                Template::set_message(lang('news_delete_success'), 'success') :
                Template::set_message(lang('news_delete_failure') . $this->news_model->error, 'error'); */
        }

        switch($filter)
        {
            case 'published':
                $where['news.news_published'] = 1;
                break;
            case 'unpublished':
                $where['news.news_published'] = 0;
                break;
            case 'deleted':
                $where['news.deleted'] = 1;
                $show_deleted = TRUE;
                break;
            case 'category':
                $category_id = (int)$this->input->get('category_id');
                $where['news.category_id'] = $category_id;

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
                #$where['news.deleted'] = 0;
                #$this->news_model->where('nedeleted', 0);
                break;
        }
        
        Template::set('filter', $filter);
        Template::set('current_url', current_url());
        
        $search_term = $this->input->post('search_term');
        if($search_term)
        {
            $this->news_model->likes('news_title', $search_term);
            //$this->news_model->likes('news_text', $search_term);
            Template::set('search_term', $search_term);
        }
        else
        {
            $this->news_model->limit($limit, $page);
        }
        $this->news_model->where($where)->select('news.id, news_title, news_slug, category_id, news_published, created_on');
        $news = $this->news_model->order_by('id', 'desc')->find_all(FALSE);
        Template::set('news', $news);
        $this->load->library('pagination');
	$this->pager['base_url'] = site_url(SITE_AREA .'/content/news/index');
        $this->pager['total_rows'] = count($news);
        $this->pager['per_page'] = $limit;
        $this->pager['uri_segment']	= 5;
        $this->pagination->initialize($this->pager);
        
        Template::set('categories', $categories);
        
        Template::render();
    }

    /**
     * Create a news
     *
     * @return void
     */
    public function create()
    {
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
            else 
            {
                Template::set_message(lang('news_create_failure') . $this->news_model->error, 'error');
            }
        }
        Template::set('categories', $this->categories_model->order_by('id', 'asc')->find_all());
        Template::set('toolbar_title', lang('news_create') . ' news');
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
            else 
            {
                Template::set_message(lang('news_edit_failure') . $this->news_model->error, 'error');
            }
        }
        Template::set('news', $this->news_model->find($id));
        Template::set('categories', $this->categories_model->order_by('id', 'asc')->find_all());
        Template::set('toolbar_title', lang('news_edit') . ' news');
        
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
        $this->form_validation->set_rules('news_slug','Slug','max_length[255]');			
        $this->form_validation->set_rules('news_text','Text','');
        $this->form_validation->set_rules('category','Category','');

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
