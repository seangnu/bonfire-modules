<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migrate_model extends CI_Model {

    /**
     * Return news count if successful.
     *
     * @param object $db database connection
     * @param string $prefix table prefix
     * @return int news count
     */
    public function count_news($db, $prefix)
    {
        return $db->count_all($prefix.'news');
    }

    /**
     * Return articles count if successful.
     *
     * @param object $db database connection
     * @param string $prefix table prefix
     * @return int articles count
     */
    public function count_articles($db, $prefix)
    {
        return $db->count_all($prefix.'articles');
    }

    /**
     * Returns the whole news table as array.
     *
     * @param object $db database connection
     * @return array the table
     */
    public function get_news($db)
    {
        return $db->get('news')->result_array();
    }

    /**
     * Returns the whole news category table as array.
     *
     * @param object $db database connection
     * @return array the table
     */
    public function get_news_categories($db)
    {
        return $db->get('news_cat')->result_array();
    }

    /**
     * Returns the whole articles table as array.
     *
     * @param object $db database connection
     * @return array the table
     */
    public function get_articles($db)
    {
        return $db->get('articles')->result_array();
    }

    /**
     * Returns the whole articles category table as array.
     *
     * @param object $db database connection
     * @return array the table
     */
    public function get_articles_categories($db)
    {
        return $db->get('articles_cat')->result_array();
    }

    /**
     * Writes all the news.
     *
     * @param array $news news table
     * @return void
     */
    public function set_news($news)
    {
        foreach($news as $n)
        {
            $slug = $this->_mkslug($n['news_title'], 'news', 'news_slug');
            $item = array('news_title' => $n['news_title'], 'news_published' => $n['news_active'], 'news_text' => $n['news_text'], 'category_id' => $n['cat_id'], 'news_slug' => $slug, 'created_on' => $this->_get_time($n['news_date']));
            $this->db->insert('news', $item);
            
            $route = array('old' => $n['news_id'], 'slug' => $slug);
            $this->db->insert('reroute', $route);

        }
    }

    /**
     * Writes all the news categories.
     *
     * @param array $news_categories news categories table
     * @return void
     */
    public function set_news_categories($news_categories)
    {
        foreach($news_categories as $n)
        {
            $slug = $this->_mkslug($n['cat_name'], 'news_categories', 'category_slug');
            $item = array('id' => $n['cat_id'], 'category_slug' => $slug, 'category_name' => $n['cat_name'], 'category_description' => $n['cat_description']);
            $this->db->insert('news_categories', $item);
        }
    }

    /**
     * Writes all the articles (as pages).
     *
     * @param array $articles articles table
     * @return void
     */
    public function set_pages($articles)
    {
        foreach($articles as $a)
        {
            $slug = $this->_mkslug($a['article_title'], 'pages', 'pages_slug');
            $item = array('pages_title' => $a['article_title'], 'pages_text' => $a['article_text'], 'category_id' => $a['article_cat_id'], 'pages_slug' => $slug, 'created_on' => $this->_get_time($a['article_date']));
            $this->db->insert('pages', $item);
            
            if( ! $a['article_url'] == NULL)
            {
                $route = array('old' => $a['article_url'], 'slug' => $slug);
                $this->db->insert('reroute', $route);
            }
        }
    }

    /**
     * Writes all the pages categories.
     *
     * @param array $articles_categories news categories table
     * @return void
     */
    public function set_pages_categories($articles_categories)
    {
        foreach($articles_categories as $n)
        {
            $item = array('id' => $n['cat_id'], 'category_name' => $n['cat_name'], 'category_description' => $n['cat_description']);
            $this->db->insert('pages_categories', $item);
        }
    }
    
    /**
     * Convert Unix Timestamp to Bonfire datetime
     * 
     * @param int $timestamp
     * @return string 
     */
    private function _get_time($timestamp)
    {
        $d = getdate($timestamp);
        return $d['year'].'-'.$d['mon'].'-'.$d['mday'];
    }
    /**
     * Returns an free slug.
     *
     * @param string $string
     * @param string $tablename the table where to check for existing slugs
     * @param string $fieldname the field where the slug is found
     * @return string the slug
     */
    private function _mkslug($string, $tablename, $fieldname)
    {
        $slug = url_title($string);
        $slug = strtolower($slug);
        $i = 0;
        $params = array ();
        $params[$fieldname] = $slug;
        if ($this->input->post('id')){
            $params['id !='] = $this->input->post('id');
        }
        
        while ($this->db->where($params)->get($tablename)->num_rows()) {
            if (!preg_match ('/-{1}[0-9]+$/', $slug ))
            {
                $slug .= '-' . ++$i;
            }
            else
            {
                $slug = preg_replace ('/[0-9]+$/', ++$i, $slug );
            }
                $params[$fieldname] = $slug;
            }
        return $slug;
    }
}
