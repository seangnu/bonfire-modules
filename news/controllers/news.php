<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class news extends Front_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;
        $this->load->model('news_model', null, true);
        $this->load->model('categories_model', null, true);
        $this->lang->load('news');
    }
	
    /**
     * Display paginated news list
     *
     * @return void
     */
    public function index($page = 0)
    {
        $config = $this->_get_config();
        $this->load->library('pagination');

        $pagination_config = array();
        $pagination_config['base_url'] = site_url('news/');
        $pagination_config['total_rows'] = $this->news_model->count_all();
        $pagination_config['per_page'] = $config['news_per_page'];
        $pagination_config['uri_segment'] = 2;

        $this->pagination->initialize($pagination_config);

        Template::set('pagination_links', $this->pagination->create_links());
        Template::set('news', $this->news_model->order_by('created_on', 'desc')->limit($config['news_per_page'], $page)->find_all_by('news_published', 1));
        Template::set('categories', $this->categories_model->find_all());
        Template::render();
    }

    /**
     * View a news
     *
     * @param string $slug
     * @return void
     */
    public function view($slug = FALSE)
    {
        if( ! $slug)
        {
            show_404();
        }
        Template::set('news', $this->news_model->find_by('news_slug', $slug));
        Template::render();
    }
    
    /**
     * Display all News of a specific category
     * 
     * @param string category slug
     * @return void 
     */
    public function category($slug, $page = 0)
    {
        if( ! $slug)
        {
            show_404();
        }
        
        $config = $this->_get_config();
        $this->load->library('pagination');
        
        $category = $this->categories_model->find_by('category_slug', $slug);
        
        $pagination_config = array();
        $pagination_config['base_url'] = site_url('news/category/'.$category->category_slug);
        $pagination_config['total_rows'] = $this->news_model->where('category_id', $category->id)->count_all();
        $pagination_config['per_page'] = $config['news_per_page'];
        $pagination_config['uri_segment'] = 4;

        $this->pagination->initialize($pagination_config); 

        Template::set('category', $category);
        Template::set('pagination_links', $this->pagination->create_links());
        Template::set('news', $this->news_model->where('news_published', 1)->order_by('created_on', 'desc')->limit($config['news_per_page'], $page)->find_all_by('category_id', $category->id));
        Template::render();
    }
    
    /**
     * Generate a RSS 2.0 Feed
     *
     * @param string $options
     * @return void
     */
    public function feed($options = FALSE)
    {
        $config = $this->_get_config();
        $data['categories'] = $this->categories_model->find_all();
        $data['news'] = $this->news_model->order_by('created_on', 'desc')->limit($config['news_per_page'])->find_all();
        $data['site_name'] = $this->config->item('site.title');
        
        $this->load->helper('xml');
        $this->config->set_item('site.auth.use_extended_profile', 0);
        header("Content-Type: application/rss+xml");
        
        switch($options)
        {
            case 'forum':
                foreach($data['news'] as &$n)
                {
                    $n->news_text = '[URL="'.site_url('news/view/'.$n->news_slug).'" title="'.$n->news_title.'"]TODO_read_article[/URL]';
                }
                unset($n);
                break;
        }
        
        $this->load->view('feed', $data);
        $this->output->enable_profiler(FALSE);
    }
    
    /**
     * Returns config array
     * 
     * @return array
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

}
