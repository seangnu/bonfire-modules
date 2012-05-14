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
        $this->load->library('pagination');

        $config['base_url'] = site_url('news/page/');
        $config['total_rows'] = $this->news_model->count_all();
        $config['per_page'] = 5; //TODO read from config

        $this->pagination->initialize($config); 

        Template::set('pagination_links', $this->pagination->create_links());
		Template::set('records', $this->news_model->order_by('created_on', 'desc')->limit('5')->find_all());
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
        $this->load->library('pagination');

        $config['base_url'] = site_url('news/page/');
        $config['total_rows'] = $this->news_model->count_all();
        $config['per_page'] = 5; //TODO read from config

        $this->pagination->initialize($config); 

        Template::set('pagination_links', $this->pagination->create_links());
		Template::set('records', $this->news_model->order_by('created_on', 'desc')->limit('5', $page)->find_all());
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

}
