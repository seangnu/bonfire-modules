<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Opcodes_model extends BF_Model {

	protected $table	= "news_opcodes";
	protected $key          = "id";
	protected $soft_deletes	= FALSE;
	protected $set_created	= FALSE;
	protected $set_modified = FALSE;
}
