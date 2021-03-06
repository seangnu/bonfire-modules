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
class news extends Front_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->load->library('form_validation');
        $this->load->model('news_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('news_frontend');
    }
	
    /**
     * Display paginated news list
     *
     * @param int $page The page.
     * @return void
     */
    public function index($page = 0)
    {
        $config = $this->_get_config();
        $this->load->library('pagination');

        $pagination_config = $this->_bootstrap_pagination_config();
        $pagination_config['base_url'] = site_url('news/');
        $pagination_config['total_rows'] = $this->news_model->count_all() -1;
        $pagination_config['per_page'] = $config['news_per_page'];
        $pagination_config['uri_segment'] = 2;

        $this->pagination->initialize($pagination_config);

        Template::set('pagination_links', $this->pagination->create_links());
        $this->news_model->where('deleted', 0)->where('news_published', 1)->select('id, news_title, news_text, news_slug, category_id, created_on');
        Template::set('news', $this->news_model->order_by('created_on', 'desc')->limit($config['news_per_page'], $page)->find_all_by('news_published', 1));
        Template::set('categories', $this->categories_model->find_all());
        Template::render();
    }

    /**
     * View a news
     *
     * @param string $slug  The slug.
     * @return void
     */
    public function view($slug = FALSE)
    {
        if( ! $slug)
        {
            redirect('news');
        }
        $news = $this->news_model->where('deleted', 0)->where('news_published', 1)->find_by('news_slug', $slug);
        if( ! $news)
        {
            show_404();
        }
        Template::set('news', $news);
        Template::render();
    }
    
    /**
     * Display all News of a specific category
     * 
     * @param string $slug  The category slug.
     * @param int $page     The page.
     * @return void 
     */
    public function category($slug = FALSE, $page = 0)
    {
        if( ! $slug)
        {
            redirect('news');
        }
        
        $config = $this->_get_config();
        $this->load->library('pagination');
        
        $category = $this->categories_model->find_by('category_slug', $slug);
        if( ! $category)
        {
            show_404();
        }
        $pagination_config = $this->_bootstrap_pagination_config();
        $pagination_config['base_url'] = site_url('news/category/'.$category->category_slug);
        $pagination_config['total_rows'] = $this->news_model->where('category_id', $category->id)->count_all() -1;
        $pagination_config['per_page'] = $config['news_per_page'];
        $pagination_config['uri_segment'] = 4;

        $this->pagination->initialize($pagination_config); 

        Template::set('category', $category);
        Template::set('pagination_links', $this->pagination->create_links());
        Template::set('news', $this->news_model->where('deleted', 0)->where('news_published', 1)->order_by('created_on', 'desc')->limit($config['news_per_page'], $page)->find_all_by('category_id', $category->id));
        Template::render();
    }
    
    /**
     * Generate a RSS 2.0 Feed
     *
     * @param string $options   Special options.
     * @return void
     */
    public function feed($options = FALSE)
    {
        $config = $this->_get_config();
        $data['categories'] = $this->categories_model->find_all();
        $data['news'] = $this->news_model->order_by('created_on', 'desc')->limit($config['news_per_page'])->find_all_by('news_published', 1);
        $data['site_name'] = $this->config->item('site.title');
        
        switch($options)
        {
            case 'forum':
                foreach($data['news'] as &$n)
                {
                    $n->news_text = '[URL="'.site_url('news/view/'.$n->news_slug).'" title="'.$n->news_title.'"]TODO_read_article[/URL]';
                }
                unset($n);
                break;
            case FALSE:
                break;
            default:
                show_404();
        }
        
        $this->load->helper('xml');
        header("Content-Type: application/rss+xml");
        $this->load->view('feed', $data);
        $this->output->enable_profiler(FALSE);
    }
    
    /**
     * Returns the news config array
     * 
     * @return array    The config array.
     */
    private function _get_config()
    {
        $config = read_config('news');
        if( ! isset($config))
        {
            show_error('TODO_please_set_settings_first');
        }
        return $config;
        
    }

    /**
     * Returns an pagination class config array suitable for the bootstrap framework
     * 
     * @return array    The config Array. 
     */
    private function _bootstrap_pagination_config()
    {
        return array('full_tag_open' => '<div class="pagination"><ul>', 'full_tag_close' => '</ul></div>', 'first_tag_open' => '<li>', 
            'first_tag_close' => '</li>', 'last_tag_open' => '<li>', 'last_tag_close' => '</li>', 'next_tag_open' => '<li>', 'next_tag_close' => '</li>', 'prev_tag_open' => '<li>',
            'prev_tag_close' => '</li>', 'cur_tag_open' => '<li class="active"><a href="#">', 'cur_tag_close' => '</a></li>', 'num_tag_open' => '<li>', 'num_tag_close' => '</li>');
    }
}
