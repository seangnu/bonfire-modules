<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class pages extends Front_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;
        $this->load->model('pages_model', null, true);
        $this->lang->load('pages');

    }
	
    public function view($slug = FALSE)
    {
        if( ! $slug) show_404();

        $page = (array)$this->pages_model->find_by('pages_slug', $slug);

        if( ! isset($page['pages_title'])) show_404();

        Template::set('page', $page);
        Template::render();
    }


}
