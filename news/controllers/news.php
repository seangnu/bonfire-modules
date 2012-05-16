<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class news extends Front_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;
        $this->load->model('news_model', null, true);
        $this->lang->load('news');
    }
	
    /**
     * Display paginated news list
     *
     * @return void
     */
    public function index()
    {
        $config = $this->_get_config();
        $this->load->library('pagination');

        $pagination_config = array();
        $pagination_config['base_url'] = site_url('news/page');
        $pagination_config['total_rows'] = $this->news_model->count_all();
        $pagination_config['per_page'] = $config['news_per_page'];

        $this->pagination->initialize($pagination_config); 

        Template::set('pagination_links', $this->pagination->create_links());
        Template::set('records', $this->news_model->order_by('created_on', 'desc')->limit($config['news_per_page'])->find_all());
        Template::render();
    }

    /**
     * Display the specified page
     *
     * @param int $page
     * @return void
     */
    public function page($page = 0)
    {
        $config = $this->_get_config();
        $this->load->library('pagination');

        $pagination_config = array();
        $pagination_config['base_url'] = site_url('news/page');
        $pagination_config['total_rows'] = $this->news_model->count_all();
        $pagination_config['per_page'] = $config['news_per_page'];

        $this->pagination->initialize($pagination_config); 

        Template::set('pagination_links', $this->pagination->create_links());
        Template::set('records', $this->news_model->order_by('created_on', 'desc')->limit($config['news_per_page'], $page)->find_all());
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
        Template::set('records', $this->news_model->find_by('news_slug', $slug));
        Template::render();
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
