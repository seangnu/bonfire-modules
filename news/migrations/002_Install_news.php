<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Install_news extends Migration {

    public function up()
    {
        $prefix = $this->db->dbprefix;

        $this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
        $this->dbforge->add_field("`news_title` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`news_slug` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`news_text` TEXT NOT NULL");
        $this->dbforge->add_field("`news_published` tinyint(1)");
        $this->dbforge->add_field("`category_id` int(11)");
        $this->dbforge->add_field("`created_on` DATETIME");
        $this->dbforge->add_field("`modified_on` DATETIME");
        $this->dbforge->add_field("`deleted` tinyint(1)");
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('news');

        $this->dbforge->add_field('`id` int(11) NOT NULL AUTO_INCREMENT');
        $this->dbforge->add_field("`category_name` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`category_slug` VARCHAR(255) NOT NULL");
        $this->dbforge->add_field("`category_description` TEXT NOT NULL");
        $this->dbforge->add_key('id', true);
        $this->dbforge->create_table('news_categories');
    }

    public function down()
    {
    $prefix = $this->db->dbprefix;

    $this->dbforge->drop_table('news');
    $this->dbforge->drop_table('news_categories');
    }

}
