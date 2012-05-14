<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Install_pages extends Migration {

	public function up()
	{
		$prefix = $this->db->dbprefix;

		$this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
		$this->dbforge->add_field("`pages_title` VARCHAR(255) NOT NULL");
		$this->dbforge->add_field("`pages_slug` VARCHAR(255) NOT NULL");
		$this->dbforge->add_field("`pages_text` TEXT NOT NULL");
        $this->dbforge->add_field("`category_id` int(11)");
        $this->dbforge->add_field("`created_on` date");
        $this->dbforge->add_field("`modified_on` date");
		$this->dbforge->add_key('id', true);
		$this->dbforge->create_table('pages');

        $this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
        $this->dbforge->add_field("`category_name` VARCHAR(255) NOT NULL");
		$this->dbforge->add_field("`category_description` TEXT NOT NULL");
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('pages_categories');

	}


	public function down()
	{
		$prefix = $this->db->dbprefix;

		$this->dbforge->drop_table('pages');
        $this->dbforge->drop_table('pages_categories');

	}


}
