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
     * @param int $page The page to display.
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
            Template::redirect(SITE_AREA .'/content/news');
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
        $news = $this->news_model->find($id);
        if( ! $news)
        {
            show_404();
        }
        Template::set('news', $news);
        Template::set('categories', $this->categories_model->order_by('id', 'asc')->find_all());
        
        Assets::add_js(Template::theme_url('js/editors/ckeditor/ckeditor.js'));
        Assets::add_js($this->load->view('content/js', null, true), 'inline');
        Template::render();
    }

    /**
     * Delete a news
     *
     * @param int $id   The news ID.
     * @return void
     */
    public function delete($id = FALSE)
    {
        $this->auth->restrict('News.Content.Edit');

        if ( ! $id === FALSE)
        {	
            if ($this->news_model->delete($id))
            {
                Template::set_message(lang('news_delete_success'), 'success');
            } else
            {
                Template::set_message(lang('news_delete_failure') . $this->news_model->error, 'error');
            }
        }
        Template::redirect(SITE_AREA .'/content/news');
    }
    
    /*
     * Display all revisions of a news
     * 
     * @param int $id   The News ID.
     * @return void
     */
    public function revisions($id = FALSE)
    {
        $this->auth->restrict('News.Content.View');
        $this->load->model('opcodes_model');
        $this->load->library('news/finediff');
        
        $news = $this->news_model->find($id);
        if( ! $news)
        {
            show_404();
        }
        Template::set('news', $news);
        
        $revisions = $this->opcodes_model->order_by('id', 'asc')->find_all_by('news_id', $id);
        foreach($revisions as &$r)
        {
            $r->htmldiff = '';
            foreach($revisions as $rev)
            {
                if($rev->id == $r->id)
                {
                    break;
                }
                $r->htmldiff = FineDiff::renderToTextFromOpcodes($r->htmldiff, $rev->opcodes);
            }
            $r->htmldiff = html_entity_decode(FineDiff::renderDiffToHTMLFromOpcodes($r->htmldiff, $r->opcodes));
        }
        unset($r);
        
        Template::set('revisions', $revisions);
        Template::render();
    }

    /**
     * Restore a revision
     * 
     * @param int $id   The Revision ID.
     * @return void
     */
    
    public function restore_revision($id)
    {
        $this->auth->restrict('News.Content.Edit');
        $this->load->model('opcodes_model');
        $this->load->library('news/finediff');
        
        $revision = $this->opcodes_model->find_by('id', $id);
        if( ! $revision)
        {
            show_404();
        }
        $revisions = $this->opcodes_model->order_by('id', 'asc')->find_all_by('news_id', $revision->news_id);
        $data = $this->news_model->find($revision->news_id);
        $news_text = '';
        foreach($revisions as $r)
        {
            $news_text = FineDiff::renderToTextFromOpcodes($news_text, $r->opcodes);
            if($r->id == $revision->id)
            {
                break;
            }
        }
        
        $opcodes = FineDiff::getDiffOpcodes($data->news_text, $news_text);
        $this->opcodes_model->insert(array('news_id' => $revision->news_id, 'opcodes' => $opcodes));
        $data->news_text = $news_text;
        
        if ($this->news_model->update($revision->news_id, (array)$data))
        {
            Template::set_message(lang('news_restore_revision_success'), 'success');
        } else
        {
            Template::set_message(lang('news_restore_revision_failure').$this->news_model->error, 'error');
        }
        Template::redirect(SITE_AREA .'/content/news/edit/'.$revision->news_id);
    }
    /**
     * Save news to database
     *
     * @param string $type  Insert or update?
     * @param int $id       If update, the news ID.
     * @return bool         TRUE for success, FALSE for fail.
     */
    private function _save_news($type='insert', $id = 0)
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
                // Save diff
                $this->load->model('opcodes_model');
                $this->load->library('news/finediff');
                $opcodes = FineDiff::getDiffOpcodes('', $data['news_text']);
                $this->opcodes_model->insert(array('news_id' => $id, 'opcodes' => $opcodes));
                return $id;
            }
            else
            {
                return FALSE;
            }
        }
        elseif ($type == 'update')
        {
            // Save diff
            $this->load->model('opcodes_model');
            $old = $this->news_model->find($id);
            $this->load->library('news/finediff');
            $opcodes = FineDiff::getDiffOpcodes($old->news_text, $data['news_text']);
            $this->opcodes_model->insert(array('news_id' => $id, 'opcodes' => $opcodes));
            return $this->news_model->update($id, $data);
        }

        return FALSE;
    }
    
    /*
     * Save selected changes to database
     * 
     * @param string $type  Possible: publish, unpublish, delete, purge
     * @param array $ids    The News IDs.
     * @return bool         TRUE if success, FALSE if fail.
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
