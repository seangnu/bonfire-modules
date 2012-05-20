<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class files extends Front_Controller {

    public function __construct() 
    {
        parent::__construct();

        $this->load->library('form_validation');
        $this->form_validation->CI =& $this;
        $this->load->model('files_model', null, true);
        $this->lang->load('files');


    }


    public function index() 
    {
        Template::set('records', $this->files_model->find_all());
        Template::render();
    }
}