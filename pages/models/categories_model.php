<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Categories_model extends BF_Model {

	protected $table		= "pages_categories";
	protected $key			= "id";
	protected $soft_deletes	= FALSE;
	protected $date_format	= "datetime";
	protected $set_created	= FALSE;
	protected $set_modified = FALSE;
}
