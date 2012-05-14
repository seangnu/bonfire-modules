<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class News_model extends BF_Model {

	protected $table		= "news";
	protected $key			= "id";
	protected $soft_deletes	= FALSE;
	protected $date_format	= "datetime";
	protected $set_created	= TRUE;
	protected $set_modified = TRUE;
}
