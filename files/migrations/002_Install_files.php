<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Install_files extends Migration {

    public function up()
    {
        $prefix = $this->db->dbprefix;

        $this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
        $this->dbforge->add_field("`file_title` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`file_name` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`file_description` TEXT NOT NULL");
        $this->dbforge->add_field("`file_size` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`file_published` tinyint(1) NOT NULL");
        $this->dbforge->add_field("`category_id` INT(11) NOT NULL");
        $this->dbforge->add_field("`is_image` tinyint(1) NOT NULL");
        $this->dbforge->add_field("`created_on` DATETIME");
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('files');
        
        $this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
        $this->dbforge->add_field("`category_name` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`category_slug` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`category_description` TEXT NOT NULL");
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('files_categories');

    }

    //--------------------------------------------------------------------

    public function down()
    {
        $prefix = $this->db->dbprefix;

        $this->dbforge->drop_table('files');
        $this->dbforge->drop_table('files_categories');

    }

    //--------------------------------------------------------------------

}