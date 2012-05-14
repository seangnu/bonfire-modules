<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class developer extends Admin_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->auth->restrict('Migrate.Developer.View');
		$this->load->model('migrate_model', null, true);
	}
	

	public function index()
	{
		Template::render();
	}

    /**
     * Check database connection and display open migration steps
     *
     * @return bool
     */
    public function check_connection()
    {
        if($this->session->userdata('all_copied') || $this->session->userdata('articles_copied') && $this->session->userdata('articles_copied'))
        {
            Template::set_message('Alles wurde erfolgreich kopiert!', 'success');
            $this->session->set_userdata('all_copied', TRUE);
            $this->session->set_userdata('articles_copied', TRUE);
            $this->session->set_userdata('articles_copied', TRUE);
        }
        if($this->input->post('hostname') != "")
        {
            // post data send, keep it
            if( ! $this->_keep_database_config())
            {
                Template::set('database_success', FALSE);
                Template::render();
                // config read from POST missing or incomplete, return
                return FALSE;
            }
        }

        $database_config = $this->_get_database_config();
        $database_connection = $this->_get_database_connection($database_config);
        if($database_connection)
        {
            $news_count = $this->migrate_model->count_news($database_connection, $database_config['dbprefix']);
            $articles_count = $this->migrate_model->count_articles($database_connection, $database_config['dbprefix']);
            if($news_count && $articles_count)
            {
                $this->session->set_userdata('database_success', TRUE);
                Template::set('database_success', TRUE);
                Template::set('news_count', $news_count);
                Template::set('articles_count', $articles_count);

                Template::set('all_copied', $this->session->userdata('all_copied'));
                Template::set('news_copied', $this->session->userdata('news_copied'));
                Template::set('articles_copied', $this->session->userdata('articles_copied'));
                
                Template::render();
                // everything cool, return
                return TRUE;
            }
        }
        // someting went wrong, error
        Template::set('database_success', FALSE);
        Template::render();
    }

    /**
     * Copy everything in one step
     *
     * @return void
     */
    public function copy_all()
    {
        $this->session->set_userdata('all_copied', TRUE);
        //TODO
        redirect('admin/developer/migrate/check_connection');
    }

    /**
     * Copy news
     *
     * @return void
     */
    public function copy_news()
    {
        $this->session->set_userdata('news_copied', TRUE);
        $database_config = $this->_get_database_config();
        $database_connection = $this->_get_database_connection($database_config);
        $news = $this->migrate_model->get_news($database_connection);
        foreach($news as &$n)
        {
            $n['news_text'] = $this->_parse_fscode($n['news_text']);
        }
        unset($n);
        $this->migrate_model->set_news($news);
        $news_categories = $this->migrate_model->get_news_categories($database_connection);
        $this->migrate_model->set_news_categories($news_categories);
        redirect('admin/developer/migrate/check_connection');
    }

    /**
     * Copy articles
     *
     * @return void
     */
    public function copy_articles()
    {
        $this->session->set_userdata('articles_copied', TRUE);
        $database_config = $this->_get_database_config();
        $database_connection = $this->_get_database_connection($database_config);
        $articles = $this->migrate_model->get_articles($database_connection);
        foreach($articles as &$a)
        {
            $a['article_text'] = $this->_parse_fscode($a['article_text']);
        }
        unset($a);
        $this->migrate_model->set_pages($articles);
        $articles_categories = $this->migrate_model->get_articles_categories($database_connection);
        $this->migrate_model->set_pages_categories($articles_categories);
        redirect('admin/developer/migrate/check_connection');
    }

    /**
     * Save database configuration from POST to session.
     *
     * @return bool
     */
    private function _keep_database_config()
    {
        if( $this->input->post('hostname') === ""
            || $this->input->post('username') === ""
            || $this->input->post('password') === ""
            || $this->input->post('database') === "")
        {
            return FALSE;
        }

        $db['hostname'] = $this->input->post('hostname');
        $db['username'] = $this->input->post('username');
        $db['password'] = $this->input->post('password');
        $db['database'] = $this->input->post('database');
        $db['dbprefix'] = $this->input->post('prefix');
        $db['dbdriver'] = 'mysql';
        $db['db_debug'] = TRUE;

        $this->session->set_userdata($db);
        return TRUE;
    }

    /**
     * Parse all FSCodes to HTML
     *
     * Does not parse video tags, smilies and ignores a lot of options
     * TODO: parse homelinks, cimg, quote
     *
     * @param string the text
     * @return string the parsed text
     */
    private function _parse_fscode($text)
    {
        $parser = new StringParser_BBCode();
        $parser->addParser('code', 'killhtml');
        $parser->addParser(array ('block', 'inline', 'link', 'listitem'), 'html_nl2br');
        $parser->addCode ('b', 'simple_replace', null, array ('start_tag' => '<b>', 'end_tag' => '</b>'),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('i', 'simple_replace', null, array ('start_tag' => '<i>', 'end_tag' => '</i>'),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('u', 'simple_replace', null, array ('start_tag' => '<span style="text-decoration:underline">', 'end_tag' => '</span>'),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('s', 'simple_replace', null, array ('start_tag' => '<span style="text-decoration:line-through">', 'end_tag' => '</span>'),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('center', 'simple_replace', null, array ('start_tag' => '<p align="center">', 'end_tag' => '</p>'),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('url', 'usecontent?', 'parse_fscode_url', array ('usecontent_param' => 'default'),
                'link', array ('listitem', 'block', 'inline'), array ('link'));
        $parser->addCode ('email', 'usecontent?', 'parse_fscode_email', array ('usecontent_param' => 'default'),
                'link', array ('listitem', 'block', 'inline'), array ('link'));
        $parser->addCode ('font', 'callback_replace', 'parse_fscode_fcs', array ('type' => "font"),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('color', 'callback_replace', 'parse_fscode_fcs', array ('type' => "color"),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('size', 'callback_replace', 'parse_fscode_fcs', array ('type' => "size"),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('img', 'usecontent?', 'parse_fscode_img', array ('usecontent_param' => 'default'),
                'image', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('noparse', 'usecontent', 'simple_usecontent_replace', array ('start_tag' => '[noparse]', 'end_tag' => '[/noparse]'),
                'inline', array ('listitem', 'block', 'inline', 'link'), array ());
        $parser->addCode ('list', 'simple_replace', null, array ('start_tag' => '<ul>', 'end_tag' => '</ul>'),
                'list', array ('block', 'listitem'), array ('link'));
        $parser->addCode ('numlist', 'simple_replace', null, array ('start_tag' => '<ol>', 'end_tag' => '</ol>'),
                'list', array ('block', 'listitem'), array ('link'));
        $parser->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
                'listitem', array ('list'), array ());
        $parser->addCode ('code', 'usecontent', 'parse_fscode_code', array (),
                'code', array ('listitem', 'block', 'inline'), array ('link'));
        $parser->addCode ('quote', 'callback_replace', 'parse_fscode_quote', array (),
                'block', array ('listitem', 'block', 'inline'), array ('link'));
        return $parser->parse($text);
    }

    public function parse_fscode_url($action, $attributes, $content, $params, $node_object)
    {
        if ($action == 'validate') return TRUE;

        if (!isset ($attributes['default']))
        {
            $url = $text = htmlspecialchars ($content);
        }
        else
        {
            $url = htmlspecialchars ($attributes['default']);
            $text = $content;
        }
        return ($url == $text) ? $url : $text . " (".$url.")";

    }
    public function parse_fscode_email($action, $attributes, $content, $params, $node_object)
    {
        if ($action == 'validate') return TRUE;

        if (!isset ($attributes['default']))
        {
            $url = $text = htmlspecialchars ($content);
        }
        else
        {
            $url = htmlspecialchars ($attributes['default']);
            $text = $content;
        }
        return '<a href="mailto:'.$url.'" target="_blank">'.$text.'</a>';  
    }

    public function parse_fscode_fcs ($action, $attributes, $content, $params, $node_object) {
    
        // validation
        if ($action == 'validate') {
            if (!isset ($attributes['default'])) { return false; }
            elseif ($params['type'] == "size") {
                $font_sizes = array(0,1,2,3,4,5,6,7);
                if (!in_array($attributes['default'], $font_sizes)) { return false; }
            }
            return true;
        }
        
        // create html/text
        if (isset ($attributes['default'])) {
            
            switch ($params['type']) {
                case "font":
                    $style = "font-family:".$attributes['default'].";";
                    break;
                
                case "color":
                    $style = "color:".$attributes['default'].";";
                    break;
                
                case "size":
                    $font_sizes_values = array("70%","85%","100%","125%","155%","195%","225%","300%");  
                    $style = "font-size:".$font_sizes_values[$attributes['default']].";";  
                    break;
                
                default:
                    $style = "";        
                    break;
            }
            
            return '<span style="'.$style.'">'.$content.'</span>';
            
        } else {
            return false;
        }
    }

    public function parse_fscode_img ($action, $attributes, $content, $params, $node_object) {
        global $FD;
        
        if ($action == 'validate') {
            return true;
        }

        // Get alt and title text
        $content_arr = array_map ( "htmlspecialchars", explode ( "|", $content, 3 ) );

        // Always provide alt-text
        if (count($content_arr) == 1) {
            $content_arr[1] = $content_arr[0];
        }
         // title shall be same like alt
        if (count($content_arr) == 3 && strlen($content_arr[2]) == 0) {
            $content_arr[2] = $content_arr[1];
        }
        $title_full = isset ($content_arr[2]) ? ' title="'.$content_arr[2].'"' : "";

        // Return html or text
        if ($params['text'] === true)
            return (isset($content_arr[2])) ? $FD->text('frontend', 'image').": ".$content_arr[2]. " (".$content_arr[0].")" : $FD->text('frontend', 'image').": ".$content_arr[0];
        else
            if (!isset ($attributes['default']))
                return '<img src="'.$content_arr[0].'" alt="'.$content_arr[1].'"'.$title_full.'>';
            else
                return '<img src="'.$content_arr[0].'" align="'.htmlspecialchars($attributes['default']).'" alt="'.$content_arr[1].'"'.$title_full.'>';
    }

    // create text lists
    function parse_fscode_textlistitems ($action, $attributes, $content, $params, $node_object) {
        if ($action == 'validate') {
            return (count($attributes) == 0);
        }
        
        $sublist = false;
            
        // Insert Item only before text child
        if (is_a($node_object->firstChild(), "StringParser_BBCode_Node_Element")
            && oneof($node_object->firstChild()->name(), 'list', 'numlist')) {
            $sublist = true;
        // If first child is text node but with no content => hide number
        } elseif ($node_object->firstChild()->type() == STRINGPARSER_NODE_TEXT) {
            if (is_empty(trim($node_object->firstChild()->content, "\n\r\0\x0B"))) {
                $sublist = true;
            }
        }
        
        // Numlist is a counter
        if (is_a($node_object->_parent, "StringParser_BBCode_Node_Element")
            && $node_object->_parent->name() == 'numlist') {
            
            $counter = 1;
            if (!is_null($node_object->_parent->attribute("counter")))
                $counter = $node_object->_parent->attribute("counter");
            
            if (!$sublist) {
                $node_object->_parent->setAttribute("counter", $counter+1);
                $params['list_item'] = sprintf($params['numlist_item'], $counter);
            }
        }    
        
        return $params['start_tag'].($sublist ? "" : $params['list_item']).$content.$params['end_tag'];
    }
    /**
     * Retrieve database config from session data.
     *
     * @return array database config
     */
    private function _get_database_config()
    {
        $db['hostname'] = $this->session->userdata('hostname');
        $db['username'] = $this->session->userdata('username');
        $db['password'] = $this->session->userdata('password');
        $db['database'] = $this->session->userdata('database');
        $db['dbprefix'] = $this->session->userdata('dbprefix');
        $db['dbdriver'] = $this->session->userdata('dbdriver');
        $db['db_debug'] = $this->session->userdata('db_debug');
        
        return $db;
    }

    /**
     * Returns an active mysql database connection.
     *
     * @param array database configuration
     * @return object database connection
     */
    private function _get_database_connection($database_config)
    {
        return $this->load->database($database_config, TRUE);
    }
}

// WARNING: Better stop reading here ;)


/**
 * Generic string parsing infrastructure
 *
 * These classes provide the means to parse any kind of string into a tree-like
 * memory structure. It would e.g. be possible to create an HTML parser based
 * upon this class.
 * 
 * Version: 0.3.3
 *
 * @author Christian Seiler <spam@christian-seiler.de>
 * @copyright Christian Seiler 2004-2008
 * @package stringparser
 *
 * The MIT License
 *
 * Copyright (c) 2004-2009 Christian Seiler
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * String parser mode: Search for the next character
 * @see StringParser::_parserMode
 */
define ('STRINGPARSER_MODE_SEARCH', 1);
/**
 * String parser mode: Look at each character of the string
 * @see StringParser::_parserMode
 */
define ('STRINGPARSER_MODE_LOOP', 2);
/**
 * Filter type: Prefilter
 * @see StringParser::addFilter, StringParser::_prefilters
 */
define ('STRINGPARSER_FILTER_PRE', 1);
/**
 * Filter type: Postfilter
 * @see StringParser::addFilter, StringParser::_postfilters
 */
define ('STRINGPARSER_FILTER_POST', 2);

/**
 * Generic string parser class
 *
 * This is an abstract class for any type of string parser.
 *
 * @package stringparser
 */
class StringParser {
	/**
	 * String parser mode
	 *
	 * There are two possible modes: searchmode and loop mode. In loop mode
	 * every single character is looked at in a loop and it is then decided
	 * what action to take. This is the most straight-forward approach to
	 * string parsing but due to the nature of PHP as a scripting language,
	 * it can also cost performance. In search mode the class posseses a
	 * list of relevant characters for parsing and uses the
	 * {@link PHP_MANUAL#strpos strpos} function to search for the next
	 * relevant character. The search mode will be faster than the loop mode
	 * in most circumstances but it is also more difficult to implement.
	 * The subclass that does the string parsing itself will define which
	 * mode it will implement.
	 *
	 * @access protected
	 * @var int
	 * @see STRINGPARSER_MODE_SEARCH, STRINGPARSER_MODE_LOOP
	 */
	var $_parserMode = STRINGPARSER_MODE_SEARCH;
	
	/**
	 * Raw text
	 * @access protected
	 * @var string
	 */
	var $_text = '';
	
	/**
	 * Parse stack
	 * @access protected
	 * @var array
	 */
	var $_stack = array ();
	
	/**
	 * Current position in raw text
	 * @access protected
	 * @var integer
	 */
	var $_cpos = -1;
	
	/**
	 * Root node
	 * @access protected
	 * @var mixed
	 */
	var $_root = null;
	
	/**
	 * Length of the text
	 * @access protected
	 * @var integer
	 */
	var $_length = -1;
	
	/**
	 * Flag if this object is already parsing a text
	 *
	 * This flag is to prevent recursive calls to the parse() function that
	 * would cause very nasty things.
	 *
	 * @access protected
	 * @var boolean
	 */
	var $_parsing = false;
	
	/**
	 * Strict mode
	 *
	 * Whether to stop parsing if a parse error occurs.
	 *
	 * @access public
	 * @var boolean
	 */
	var $strict = false;
	
	/**
	 * Characters or strings to look for
	 * @access protected
	 * @var array
	 */
	var $_charactersSearch = array ();
	
	/**
	 * Characters currently allowed
	 *
	 * Note that this will only be evaluated in loop mode; in search mode
	 * this would ruin every performance increase. Note that only single
	 * characters are permitted here, no strings. Please also note that in
	 * loop mode, {@link StringParser::_charactersSearch _charactersSearch}
	 * is evaluated before this variable.
	 *
	 * If in strict mode, parsing is stopped if a character that is not
	 * allowed is encountered. If not in strict mode, the character is
	 * simply ignored.
	 *
	 * @access protected
	 * @var array
	 */
	var $_charactersAllowed = array ();
	
	/**
	 * Current parser status
	 * @access protected
	 * @var int
	 */
	var $_status = 0;
	
	/**
	 * Prefilters
	 * @access protected
	 * @var array
	 */
	var $_prefilters = array ();
	
	/**
	 * Postfilters
	 * @access protected
	 * @var array
	 */
	var $_postfilters = array ();
	
	/**
	 * Recently reparsed?
	 * @access protected
	 * @var bool
	 */
	var $_recentlyReparsed = false;
	 
	/**
	 * Constructor
	 *
	 * @access public
	 */
	function StringParser () {
	}
	
	/**
	 * Add a filter
	 *
	 * @access public
	 * @param int $type The type of the filter
	 * @param mixed $callback The callback to call
	 * @return bool
	 * @see STRINGPARSER_FILTER_PRE, STRINGPARSER_FILTER_POST
	 */
	function addFilter ($type, $callback) {
		// make sure the function is callable
		if (!is_callable ($callback)) {
			return false;
		}
		
		switch ($type) {
			case STRINGPARSER_FILTER_PRE:
				$this->_prefilters[] = $callback;
				break;
			case STRINGPARSER_FILTER_POST:
				$this->_postfilters[] = $callback;
				break;
			default:
				return false;
		}
		
		return true;
	}
	
	/**
	 * Remove all filters
	 *
	 * @access public
	 * @param int $type The type of the filter or 0 for all
	 * @return bool
	 * @see STRINGPARSER_FILTER_PRE, STRINGPARSER_FILTER_POST
	 */
	function clearFilters ($type = 0) {
		switch ($type) {
			case 0:
				$this->_prefilters = array ();
				$this->_postfilters = array ();
				break;
			case STRINGPARSER_FILTER_PRE:
				$this->_prefilters = array ();
				break;
			case STRINGPARSER_FILTER_POST:
				$this->_postfilters = array ();
				break;
			default:
				return false;
		}
		return true;
	}
	
	/**
	 * This function parses the text
	 *
	 * @access public
	 * @param string $text The text to parse
	 * @return mixed Either the root object of the tree if no output method
	 *               is defined, the tree reoutput to e.g. a string or false
	 *               if an internal error occured, such as a parse error if
	 *               in strict mode or the object is already parsing a text.
	 */
	function parse ($text) {
		if ($this->_parsing) {
			return false;
		}
		$this->_parsing = true;
		$this->_text = $this->_applyPrefilters ($text);
		$this->_output = null;
		$this->_length = strlen ($this->_text);
		$this->_cpos = 0;
		unset ($this->_stack);
		$this->_stack = array ();
		if (is_object ($this->_root)) {
			StringParser_Node::destroyNode ($this->_root);
		}
		unset ($this->_root);
		$this->_root = new StringParser_Node_Root ();
		$this->_stack[0] =& $this->_root;
		
		$this->_parserInit ();
		
		$finished = false;
		
		while (!$finished) {
			switch ($this->_parserMode) {
				case STRINGPARSER_MODE_SEARCH:
					$res = $this->_searchLoop ();
					if (!$res) {
						$this->_parsing = false;
						return false;
					}
					break;
				case STRINGPARSER_MODE_LOOP:
					$res = $this->_loop ();
					if (!$res) {
						$this->_parsing = false;
						return false;
					}
					break;
				default:
					$this->_parsing = false;
					return false;
			}
			
			$res = $this->_closeRemainingBlocks ();
			if (!$res) {
				if ($this->strict) {
					$this->_parsing = false;
					return false;
				} else {
					$res = $this->_reparseAfterCurrentBlock ();
					if (!$res) {
						$this->_parsing = false;
						return false;
					}
					continue;
				}
			}
			$finished = true;
		}
		
		$res = $this->_modifyTree ();
		
		if (!$res) {
			$this->_parsing = false;
			return false;
		}
		
		$res = $this->_outputTree ();
		
		if (!$res) {
			$this->_parsing = false;
			return false;
		}
		
		if (is_null ($this->_output)) {
			$root =& $this->_root;
			unset ($this->_root);
			$this->_root = null;
			while (count ($this->_stack)) {
				unset ($this->_stack[count($this->_stack)-1]);
			}
			$this->_stack = array ();
			$this->_parsing = false;
			return $root;
		}
		
		$res = StringParser_Node::destroyNode ($this->_root);
		if (!$res) {
			$this->_parsing = false;
			return false;
		}
		unset ($this->_root);
		$this->_root = null;
		while (count ($this->_stack)) {
			unset ($this->_stack[count($this->_stack)-1]);
		}
		$this->_stack = array ();
		
		$this->_parsing = false;
		return $this->_output;
	}
	
	/**
	 * Apply prefilters
	 *
	 * It is possible to specify prefilters for the parser to do some
	 * manipulating of the string beforehand.
	 */
	function _applyPrefilters ($text) {
		foreach ($this->_prefilters as $filter) {
			if (is_callable ($filter)) {
				$ntext = call_user_func ($filter, $text);
				if (is_string ($ntext)) {
					$text = $ntext;
				}
			}
		}
		return $text;
	}
	
	/**
	 * Apply postfilters
	 *
	 * It is possible to specify postfilters for the parser to do some
	 * manipulating of the string afterwards.
	 */
	function _applyPostfilters ($text) {
		foreach ($this->_postfilters as $filter) {
			if (is_callable ($filter)) {
				$ntext = call_user_func ($filter, $text);
				if (is_string ($ntext)) {
					$text = $ntext;
				}
			}
		}
		return $text;
	}
	
	/**
	 * Abstract method: Manipulate the tree
	 * @access protected
	 * @return bool
	 */
	function _modifyTree () {
		return true;
	}
	
	/**
	 * Abstract method: Output tree
	 * @access protected
	 * @return bool
	 */
	function _outputTree () {
		// this could e.g. call _applyPostfilters
		return true;
	}
	
	/**
	 * Restart parsing after current block
	 *
	 * To achieve this the current top stack object is removed from the
	 * tree. Then the current item
	 *
	 * @access protected
	 * @return bool
	 */
	function _reparseAfterCurrentBlock () {
		// this should definitely not happen!
		if (($stack_count = count ($this->_stack)) < 2) {
			return false;
		}
		$topelem =& $this->_stack[$stack_count-1];
		
		$node_parent =& $topelem->_parent;
		// remove the child from the tree
		$res = $node_parent->removeChild ($topelem, false);
		if (!$res) {
			return false;
		}
		$res = $this->_popNode ();
		if (!$res) {
			return false;
		}
		
		// now try to get the position of the object
		if ($topelem->occurredAt < 0) {
			return false;
		}
		// HACK: could it be necessary to set a different status
		// if yes, how should this be achieved? Another member of
		// StringParser_Node?
		$this->_setStatus (0);
		$res = $this->_appendText ($this->_text{$topelem->occurredAt});
		if (!$res) {
			return false;
		}
		
		$this->_cpos = $topelem->occurredAt + 1;
		$this->_recentlyReparsed = true;
		
		return true;
	}
	
	/**
	 * Abstract method: Close remaining blocks
	 * @access protected
	 */
	function _closeRemainingBlocks () {
		// everything closed
		if (count ($this->_stack) == 1) {
			return true;
		}
		// not everything closed
		if ($this->strict) {
			return false;
		}
		while (count ($this->_stack) > 1) {
			$res = $this->_popNode ();
			if (!$res) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Abstract method: Initialize the parser
	 * @access protected
	 */
	function _parserInit () {
		$this->_setStatus (0);
	}
	
	/**
	 * Abstract method: Set a specific status
	 * @access protected
	 */
	function _setStatus ($status) {
		if ($status != 0) {
			return false;
		}
		$this->_charactersSearch = array ();
		$this->_charactersAllowed = array ();
		$this->_status = $status;
		return true;
	}
	
	/**
	 * Abstract method: Handle status
	 * @access protected
	 * @param int $status The current status
	 * @param string $needle The needle that was found
	 * @return bool
	 */
	function _handleStatus ($status, $needle) {
		$this->_appendText ($needle);
		$this->_cpos += strlen ($needle);
		return true;
	}
	
	/**
	 * Search mode loop
	 * @access protected
	 * @return bool
	 */
	function _searchLoop () {
		$i = 0;
		while (1) {
			// make sure this is false!
			$this->_recentlyReparsed = false;
			
			list ($needle, $offset) = $this->_strpos ($this->_charactersSearch, $this->_cpos);
			// parser ends here
			if ($needle === false) {
				// original status 0 => no problem
				if (!$this->_status) {
					break;
				}
				// not in original status? strict mode?
				if ($this->strict) {
					return false;
				}
				// break up parsing operation of current node
				$res = $this->_reparseAfterCurrentBlock ();
				if (!$res) {
					return false;
				}
				continue;
			}
			// get subtext
			$subtext = substr ($this->_text, $this->_cpos, $offset - $this->_cpos);
			$res = $this->_appendText ($subtext);
			if (!$res) {
				return false;
			}
			$this->_cpos = $offset;
			$res = $this->_handleStatus ($this->_status, $needle);
			if (!$res && $this->strict) {
				return false;
			}
			if (!$res) {
				$res = $this->_appendText ($this->_text{$this->_cpos});
				if (!$res) {
					return false;
				}
				$this->_cpos++;
				continue;
			}
			if ($this->_recentlyReparsed) {
				$this->_recentlyReparsed = false;
				continue;
			}
			$this->_cpos += strlen ($needle);
		}
		
		// get subtext
		if ($this->_cpos < strlen ($this->_text)) {
			$subtext = substr ($this->_text, $this->_cpos);
			$res = $this->_appendText ($subtext);
			if (!$res) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Loop mode loop
	 *
	 * @access protected
	 * @return bool
	 */
	function _loop () {
		// HACK: This method ist not yet implemented correctly, the code below
		// DOES NOT WORK! Do not use!
		
		return false;
		/*
		while ($this->_cpos < $this->_length) {
			$needle = $this->_strDetect ($this->_charactersSearch, $this->_cpos);
			
			if ($needle === false) {
				// not found => see if character is allowed
				if (!in_array ($this->_text{$this->_cpos}, $this->_charactersAllowed)) {
					if ($strict) {
						return false;
					}
					// ignore
					continue;
				}
				// lot's of FIXMES
				$res = $this->_appendText ($this->_text{$this->_cpos});
				if (!$res) {
					return false;
				}
			}
			
			// get subtext
			$subtext = substr ($this->_text, $offset, $offset - $this->_cpos);
			$res = $this->_appendText ($subtext);
			if (!$res) {
				return false;
			}
			$this->_cpos = $subtext;
			$res = $this->_handleStatus ($this->_status, $needle);
			if (!$res && $strict) {
				return false;
			}
		}
		// original status 0 => no problem
		if (!$this->_status) {
			return true;
		}
		// not in original status? strict mode?
		if ($this->strict) {
			return false;
		}
		// break up parsing operation of current node
		$res = $this->_reparseAfterCurrentBlock ();
		if (!$res) {
			return false;
		}
		// this will not cause an infinite loop because
		// _reparseAfterCurrentBlock will increase _cpos by one!
		return $this->_loop ();
		*/
	}
	
	/**
	 * Abstract method Append text depending on current status
	 * @access protected
	 * @param string $text The text to append
	 * @return bool On success, the function returns true, else false
	 */
	function _appendText ($text) {
		if (!strlen ($text)) {
			return true;
		}
		// default: call _appendToLastTextChild
		return $this->_appendToLastTextChild ($text);
	}
	
	/**
	 * Append text to last text child of current top parser stack node
	 * @access protected
	 * @param string $text The text to append
	 * @return bool On success, the function returns true, else false
	 */
	function _appendToLastTextChild ($text) {
		$scount = count ($this->_stack);
		if ($scount == 0) {
			return false;
		}
		return $this->_stack[$scount-1]->appendToLastTextChild ($text);
	}
	
	/**
	 * Searches {@link StringParser::_text _text} for every needle that is
	 * specified by using the {@link PHP_MANUAL#strpos strpos} function. It
	 * returns an associative array with the key <code>'needle'</code>
	 * pointing at the string that was found first and the key
	 * <code>'offset'</code> pointing at the offset at which the string was
	 * found first. If no needle was found, the <code>'needle'</code>
	 * element is <code>false</code> and the <code>'offset'</code> element
	 * is <code>-1</code>.
	 *
	 * @access protected
	 * @param array $needles
	 * @param int $offset
	 * @return array
	 * @see StringParser::_text
	 */
	function _strpos ($needles, $offset) {
		$cur_needle = false;
		$cur_offset = -1;
		
		if ($offset < strlen ($this->_text)) {
			foreach ($needles as $needle) {
				$n_offset = strpos ($this->_text, $needle, $offset);
				if ($n_offset !== false && ($n_offset < $cur_offset || $cur_offset < 0)) {
					$cur_needle = $needle;
					$cur_offset = $n_offset;
				}
			}
		}
		
		return array ($cur_needle, $cur_offset, 'needle' => $cur_needle, 'offset' => $cur_offset);
	}
	
	/**
	 * Detects a string at the current position
	 *
	 * @access protected
	 * @param array $needles The strings that are to be detected
	 * @param int $offset The current offset
	 * @return mixed The string that was detected or the needle
	 */
	function _strDetect ($needles, $offset) {
		foreach ($needles as $needle) {
			$l = strlen ($needle);
			if (substr ($this->_text, $offset, $l) == $needle) {
				return $needle;
			}
		}
		return false;
	}
	
	
	/**
	 * Adds a node to the current parse stack
	 *
	 * @access protected
	 * @param object $node The node that is to be added
	 * @return bool True on success, else false.
	 * @see StringParser_Node, StringParser::_stack
	 */
	function _pushNode (&$node) {
		$stack_count = count ($this->_stack);
		$max_node =& $this->_stack[$stack_count-1];
		if (!$max_node->appendChild ($node)) {
			return false;
		}
		$this->_stack[$stack_count] =& $node;
		return true;
	}
	
	/**
	 * Removes a node from the current parse stack
	 *
	 * @access protected
	 * @return bool True on success, else false.
	 * @see StringParser_Node, StringParser::_stack
	 */
	function _popNode () {
		$stack_count = count ($this->_stack);
		unset ($this->_stack[$stack_count-1]);
		return true;
	}
	
	/**
	 * Execute a method on the top element
	 *
	 * @access protected
	 * @return mixed
	 */
	function _topNode () {
		$args = func_get_args ();
		if (!count ($args)) {
			return; // oops?
		}
		$method = array_shift ($args);
		$stack_count = count ($this->_stack);
		$method = array (&$this->_stack[$stack_count-1], $method);
		if (!is_callable ($method)) {
			return; // oops?
		}
		return call_user_func_array ($method, $args);
	}
	
	/**
	 * Get a variable of the top element
	 *
	 * @access protected
	 * @return mixed
	 */
	function _topNodeVar ($var) {
		$stack_count = count ($this->_stack);
		return $this->_stack[$stack_count-1]->$var;
	}
}

/**
 * Node type: Unknown node
 * @see StringParser_Node::_type
 */
define ('STRINGPARSER_NODE_UNKNOWN', 0);

/**
 * Node type: Root node
 * @see StringParser_Node::_type
 */
define ('STRINGPARSER_NODE_ROOT', 1);

/**
 * Node type: Text node
 * @see StringParser_Node::_type
 */
define ('STRINGPARSER_NODE_TEXT', 2);

/**
 * Global value that is a counter of string parser node ids. Compare it to a
 * sequence in databases.
 * @var int
 */
$GLOBALS['__STRINGPARSER_NODE_ID'] = 0;

/**
 * Generic string parser node class
 *
 * This is an abstract class for any type of node that is used within the
 * string parser. General warning: This class contains code regarding references
 * that is very tricky. Please do not touch this code unless you exactly know
 * what you are doing. Incorrect handling of references may cause PHP to crash
 * with a segmentation fault! You have been warned.
 *
 * @package stringparser
 */
class StringParser_Node {
	/**
	 * The type of this node.
	 * 
	 * There are three standard node types: root node, text node and unknown
	 * node. All node types are integer constants. Any node type of a
	 * subclass must be at least 32 to allow future developements.
	 *
	 * @access protected
	 * @var int
	 * @see STRINGPARSER_NODE_ROOT, STRINGPARSER_NODE_TEXT
	 * @see STRINGPARSER_NODE_UNKNOWN
	 */
	var $_type = STRINGPARSER_NODE_UNKNOWN;
	
	/**
	 * The node ID
	 *
	 * This ID uniquely identifies this node. This is needed when searching
	 * for a specific node in the children array. Please note that this is
	 * only an internal variable and should never be used - not even in
	 * subclasses and especially not in external data structures. This ID
	 * has nothing to do with any type of ID in HTML oder XML.
	 *
	 * @access protected
	 * @var int
	 * @see StringParser_Node::_children
	 */
	var $_id = -1;
	
	/**
	 * The parent of this node.
	 *
	 * It is either null (root node) or a reference to the parent object.
	 *
	 * @access protected
	 * @var mixed
	 * @see StringParser_Node::_children
	 */
	var $_parent = null;
	
	/**
	 * The children of this node.
	 *
	 * It contains an array of references to all the children nodes of this
	 * node.
	 *
	 * @access protected
	 * @var array
	 * @see StringParser_Node::_parent
	 */
	var $_children = array ();
	
	/**
	 * Occured at
	 *
	 * This defines the position in the parsed text where this node occurred
	 * at. If -1, this value was not possible to be determined.
	 *
	 * @access public
	 * @var int
	 */
	var $occurredAt = -1;
	
	/**
	 * Constructor
	 *
	 * Currently, the constructor only allocates a new ID for the node and
	 * assigns it.
	 *
	 * @access public
	 * @param int $occurredAt The position in the text where this node
	 *                        occurred at. If not determinable, it is -1.
	 * @global __STRINGPARSER_NODE_ID
	 */
	function StringParser_Node ($occurredAt = -1) {
		$this->_id = $GLOBALS['__STRINGPARSER_NODE_ID']++;
		$this->occurredAt = $occurredAt;
	}
	
	/**
	 * Type of the node
	 *
	 * This function returns the type of the node
	 *
	 * @access public
	 * @return int
	 */
	function type () {
		return $this->_type;
	}
	
	/**
	 * Prepend a node
	 *
	 * @access public
	 * @param object $node The node to be prepended.
	 * @return bool On success, the function returns true, else false.
	 */
	function prependChild (&$node) {
		if (!is_object ($node)) {
			return false;
		}
		
		// root nodes may not be children of other nodes!
		if ($node->_type == STRINGPARSER_NODE_ROOT) {
			return false;
		}
		
		// if node already has a parent
		if ($node->_parent !== false) {
			// remove node from there
			$parent =& $node->_parent;
			if (!$parent->removeChild ($node, false)) {
				return false;
			}
			unset ($parent);
		}
		
		$index = count ($this->_children) - 1;
		// move all nodes to a new index
		while ($index >= 0) {
			// save object
			$object =& $this->_children[$index];
			// we have to unset it because else it will be
			// overridden in in the loop
			unset ($this->_children[$index]);
			// put object to new position
			$this->_children[$index+1] =& $object;
			$index--;
		}
		$this->_children[0] =& $node;
		return true;
	}
	
	/**
	 * Append text to last text child
	 * @access public
	 * @param string $text The text to append
	 * @return bool On success, the function returns true, else false
	 */
	function appendToLastTextChild ($text) {
		$ccount = count ($this->_children);
		if ($ccount == 0 || $this->_children[$ccount-1]->_type != STRINGPARSER_NODE_TEXT) {
			$ntextnode = new StringParser_Node_Text ($text);
			return $this->appendChild ($ntextnode);
		} else {
			$this->_children[$ccount-1]->appendText ($text);
			return true;
		}
	}
	
	/**
	 * Append a node to the children
	 *
	 * This function appends a node to the children array(). It
	 * automatically sets the {@link StrinParser_Node::_parent _parent}
	 * property of the node that is to be appended.
	 *
	 * @access public
	 * @param object $node The node that is to be appended.
	 * @return bool On success, the function returns true, else false.
	 */
	function appendChild (&$node) {
		if (!is_object ($node)) {
			return false;
		}
		
		// root nodes may not be children of other nodes!
		if ($node->_type == STRINGPARSER_NODE_ROOT) {
			return false;
		}
		
		// if node already has a parent
		if ($node->_parent !== null) {
			// remove node from there
			$parent =& $node->_parent;
			if (!$parent->removeChild ($node, false)) {
				return false;
			}
			unset ($parent);
		}
		
		// append it to current node
		$new_index = count ($this->_children);
		$this->_children[$new_index] =& $node;
		$node->_parent =& $this;
		return true;
	}
	
	/**
	 * Insert a node before another node
	 *
	 * @access public
	 * @param object $node The node to be inserted.
	 * @param object $reference The reference node where the new node is
	 *                          to be inserted before.
	 * @return bool On success, the function returns true, else false.
	 */
	function insertChildBefore (&$node, &$reference) {
		if (!is_object ($node)) {
			return false;
		}
		
		// root nodes may not be children of other nodes!
		if ($node->_type == STRINGPARSER_NODE_ROOT) {
			return false;
		}
		
		// is the reference node a child?
		$child = $this->_findChild ($reference);
		
		if ($child === false) {
			return false;
		}
		
		// if node already has a parent
		if ($node->_parent !== null) {
			// remove node from there
			$parent =& $node->_parent;
			if (!$parent->removeChild ($node, false)) {
				return false;
			}
			unset ($parent);
		}
		
		$index = count ($this->_children) - 1;
		// move all nodes to a new index
		while ($index >= $child) {
			// save object
			$object =& $this->_children[$index];
			// we have to unset it because else it will be
			// overridden in in the loop
			unset ($this->_children[$index]);
			// put object to new position
			$this->_children[$index+1] =& $object;
			$index--;
		}
		$this->_children[$child] =& $node;
		return true;
	}
	
	/**
	 * Insert a node after another node
	 *
	 * @access public
	 * @param object $node The node to be inserted.
	 * @param object $reference The reference node where the new node is
	 *                          to be inserted after.
	 * @return bool On success, the function returns true, else false.
	 */
	function insertChildAfter (&$node, &$reference) {
		if (!is_object ($node)) {
			return false;
		}
		
		// root nodes may not be children of other nodes!
		if ($node->_type == STRINGPARSER_NODE_ROOT) {
			return false;
		}
		
		// is the reference node a child?
		$child = $this->_findChild ($reference);
		
		if ($child === false) {
			return false;
		}
		
		// if node already has a parent
		if ($node->_parent !== false) {
			// remove node from there
			$parent =& $node->_parent;
			if (!$parent->removeChild ($node, false)) {
				return false;
			}
			unset ($parent);
		}
		
		$index = count ($this->_children) - 1;
		// move all nodes to a new index
		while ($index >= $child + 1) {
			// save object
			$object =& $this->_children[$index];
			// we have to unset it because else it will be
			// overridden in in the loop
			unset ($this->_children[$index]);
			// put object to new position
			$this->_children[$index+1] =& $object;
			$index--;
		}
		$this->_children[$child + 1] =& $node;
		return true;
	}
	
	/**
	 * Remove a child node
	 *
	 * This function removes a child from the children array. A parameter
	 * tells the function whether to destroy the child afterwards or not.
	 * If the specified node is not a child of this node, the function will
	 * return false.
	 *
	 * @access public
	 * @param mixed $child The child to destroy; either an integer
	 *                     specifying the index of the child or a reference
	 *                     to the child itself.
	 * @param bool $destroy Destroy the child afterwards.
	 * @return bool On success, the function returns true, else false.
	 */
	function removeChild (&$child, $destroy = false) {
		if (is_object ($child)) {
			// if object: get index
			$object =& $child;
			unset ($child);
			$child = $this->_findChild ($object);
			if ($child === false) {
				return false;
			}
		} else {
			// remove reference on $child
			$save = $child;
			unset($child);
			$child = $save;
			
			// else: get object
			if (!isset($this->_children[$child])) {
				return false;
			}
			$object =& $this->_children[$child];
		}
		
		// store count for later use
		$ccount = count ($this->_children);
		
		// index out of bounds
		if (!is_int ($child) || $child < 0 || $child >= $ccount) {
			return false;
		}
		
		// inkonsistency
		if ($this->_children[$child]->_parent === null ||
		    $this->_children[$child]->_parent->_id != $this->_id) {
			return false;
		}
		
		// $object->_parent = null would equal to $this = null
		// as $object->_parent is a reference to $this!
		// because of this, we have to unset the variable to remove
		// the reference and then redeclare the variable
		unset ($object->_parent); $object->_parent = null;
		
		// we have to unset it because else it will be overridden in
		// in the loop
		unset ($this->_children[$child]);
		
		// move all remaining objects one index higher
		while ($child < $ccount - 1) {
			// save object
			$obj =& $this->_children[$child+1];
			// we have to unset it because else it will be
			// overridden in in the loop
			unset ($this->_children[$child+1]);
			// put object to new position
			$this->_children[$child] =& $obj;
			// UNSET THE OBJECT!
			unset ($obj);
			$child++;
		}
		
		if ($destroy) {
			return StringParser_Node::destroyNode ($object);
			unset ($object);
		}
		return true;
	}
	
	/**
	 * Get the first child of this node
	 *
	 * @access public
	 * @return mixed
	 */
	function &firstChild () {
		$ret = null;
		if (!count ($this->_children)) {
			return $ret;
		}
		return $this->_children[0];
	}
	
	/**
	 * Get the last child of this node
	 *
	 * @access public
	 * @return mixed
	 */
	function &lastChild () {
		$ret = null;
		$c = count ($this->_children);
		if (!$c) {
			return $ret;
		}
		return $this->_children[$c-1];
	}
	
	/**
	 * Destroy a node
	 *
	 * @access public
	 * @static
	 * @param object $node The node to destroy
	 * @return bool True on success, else false.
	 */
	function destroyNode (&$node) {
		if ($node === null) {
			return false;
		}
		// if parent exists: remove node from tree!
		if ($node->_parent !== null) {
			$parent =& $node->_parent;
			// directly return that result because the removeChild
			// method will call destroyNode again
			return $parent->removeChild ($node, true);
		}
		
		// node has children
		while (count ($node->_children)) {
			$child = 0;
			// remove first child until no more children remain
			if (!$node->removeChild ($child, true)) {
				return false;
			}
			unset($child);
		}
		
		// now call the nodes destructor
		if (!$node->_destroy ()) {
			return false;
		}
		
		// now just unset it and prey that there are no more references
		// to this node
		unset ($node);
		
		return true;
	}
	
	/**
	 * Destroy this node
	 *
	 *
	 * @access protected
	 * @return bool True on success, else false.
	 */
	function _destroy () {
		return true;
	}
	
	/** 
	 * Find a child node
	 *
	 * This function searches for a node in the own children and returns
	 * the index of the node or false if the node is not a child of this
	 * node.
	 *
	 * @access protected
	 * @param mixed $child The node to look for.
	 * @return mixed The index of the child node on success, else false.
	 */
	function _findChild (&$child) {
		if (!is_object ($child)) {
			return false;
		}
		
		$ccount = count ($this->_children);
		for ($i = 0; $i < $ccount; $i++) {
			if ($this->_children[$i]->_id == $child->_id) {
				return $i;
			}
		}
		
		return false;
	}
	
	/** 
	 * Checks equality of this node and another node
	 *
	 * @access public
	 * @param mixed $node The node to be compared with
	 * @return bool True if the other node equals to this node, else false.
	 */
	function equals (&$node) {
		return ($this->_id == $node->_id);
	}
	
	/**
	 * Determines whether a criterium matches this node
	 *
	 * @access public
	 * @param string $criterium The criterium that is to be checked
	 * @param mixed $value The value that is to be compared
	 * @return bool True if this node matches that criterium
	 */
	function matchesCriterium ($criterium, $value) {
		return false;
	}
	
	/**
	 * Search for nodes with a certain criterium
	 *
	 * This may be used to implement getElementsByTagName etc.
	 *
	 * @access public
	 * @param string $criterium The criterium that is to be checked
	 * @param mixed $value The value that is to be compared
	 * @return array All subnodes that match this criterium
	 */
	function &getNodesByCriterium ($criterium, $value) {
		$nodes = array ();
		$node_ctr = 0;
		for ($i = 0; $i < count ($this->_children); $i++) {
			if ($this->_children[$i]->matchesCriterium ($criterium, $value)) {
				$nodes[$node_ctr++] =& $this->_children[$i];
			}
			$subnodes = $this->_children[$i]->getNodesByCriterium ($criterium, $value);
			if (count ($subnodes)) {
				$subnodes_count = count ($subnodes);
				for ($j = 0; $j < $subnodes_count; $j++) {
					$nodes[$node_ctr++] =& $subnodes[$j];
					unset ($subnodes[$j]);
				}
			}
			unset ($subnodes);
		}
		return $nodes;
	}
	
	/**
	 * Search for nodes with a certain criterium and return the count
	 *
	 * Similar to getNodesByCriterium
	 *
	 * @access public
	 * @param string $criterium The criterium that is to be checked
	 * @param mixed $value The value that is to be compared
	 * @return int The number of subnodes that match this criterium
	 */
	function getNodeCountByCriterium ($criterium, $value) {
		$node_ctr = 0;
		for ($i = 0; $i < count ($this->_children); $i++) {
			if ($this->_children[$i]->matchesCriterium ($criterium, $value)) {
				$node_ctr++;
			}
			$subnodes = $this->_children[$i]->getNodeCountByCriterium ($criterium, $value);
			$node_ctr += $subnodes;
		}
		return $node_ctr;
	}
	
	/**
	 * Dump nodes
	 *
	 * This dumps a tree of nodes
	 *
	 * @access public
	 * @param string $prefix The prefix that is to be used for indentation
	 * @param string $linesep The line separator
	 * @param int $level The initial level of indentation
	 * @return string
	 */
	function dump ($prefix = " ", $linesep = "\n", $level = 0) {
		$str = str_repeat ($prefix, $level) . $this->_id . ": " . $this->_dumpToString () . $linesep;
		for ($i = 0; $i < count ($this->_children); $i++) {
			$str .= $this->_children[$i]->dump ($prefix, $linesep, $level + 1);
		}
		return $str;
	}
	
	/**
	 * Dump this node to a string
	 *
	 * @access protected
	 * @return string
	 */
	function _dumpToString () {
		if ($this->_type == STRINGPARSER_NODE_ROOT) {
			return "root";
		}
		return (string)$this->_type;
	}
}

/**
 * String parser root node class
 *
 * @package stringparser
 */
class StringParser_Node_Root extends StringParser_Node {
	/**
	 * The type of this node.
	 * 
	 * This node is a root node.
	 *
	 * @access protected
	 * @var int
	 * @see STRINGPARSER_NODE_ROOT
	 */
	var $_type = STRINGPARSER_NODE_ROOT;
}

/**
 * String parser text node class
 *
 * @package stringparser
 */
class StringParser_Node_Text extends StringParser_Node {
	/**
	 * The type of this node.
	 * 
	 * This node is a text node.
	 *
	 * @access protected
	 * @var int
	 * @see STRINGPARSER_NODE_TEXT
	 */
	var $_type = STRINGPARSER_NODE_TEXT;
	
	/**
	 * Node flags
	 * 
	 * @access protected
	 * @var array
	 */
	var $_flags = array ();
	
	/**
	 * The content of this node
	 * @access public
	 * @var string
	 */
	var $content = '';
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param string $content The initial content of this element
	 * @param int $occurredAt The position in the text where this node
	 *                        occurred at. If not determinable, it is -1.
	 * @see StringParser_Node_Text::content
	 */
	function StringParser_Node_Text ($content, $occurredAt = -1) {
		parent::StringParser_Node ($occurredAt);
		$this->content = $content;
	}
	
	/**
	 * Append text to content
	 *
	 * @access public
	 * @param string $text The text to append
	 * @see StringParser_Node_Text::content
	 */
	function appendText ($text) {
		$this->content .= $text;
	}
	
	/**
	 * Set a flag
	 *
	 * @access public
	 * @param string $name The name of the flag
	 * @param mixed $value The value of the flag
	 */
	function setFlag ($name, $value) {
		$this->_flags[$name] = $value;
		return true;
	}
	
	/**
	 * Get Flag
	 *
	 * @access public
	 * @param string $flag The requested flag
	 * @param string $type The requested type of the return value
	 * @param mixed $default The default return value
	 */
	function getFlag ($flag, $type = 'mixed', $default = null) {
		if (!isset ($this->_flags[$flag])) {
			return $default;
		}
		$return = $this->_flags[$flag];
		if ($type != 'mixed') {
			settype ($return, $type);
		}
		return $return;
	}
	
	/**
	 * Dump this node to a string
	 */
	function _dumpToString () {
		return "text \"".substr (preg_replace ('/\s+/', ' ', $this->content), 0, 40)."\" [f:".preg_replace ('/\s+/', ' ', join(':', array_keys ($this->_flags)))."]";
	}
}

/**
 * BB code string parsing class
 *
 * Version: 0.3.3
 *
 * @author Christian Seiler <spam@christian-seiler.de>
 * @copyright Christian Seiler 2004-2008
 * @package stringparser
 *
 * The MIT License
 *
 * Copyright (c) 2004-2008 Christian Seiler
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
 

define ('BBCODE_CLOSETAG_FORBIDDEN', -1);
define ('BBCODE_CLOSETAG_OPTIONAL', 0);
define ('BBCODE_CLOSETAG_IMPLICIT', 1);
define ('BBCODE_CLOSETAG_IMPLICIT_ON_CLOSE_ONLY', 2);
define ('BBCODE_CLOSETAG_MUSTEXIST', 3);

define ('BBCODE_NEWLINE_PARSE', 0);
define ('BBCODE_NEWLINE_IGNORE', 1);
define ('BBCODE_NEWLINE_DROP', 2);

define ('BBCODE_PARAGRAPH_ALLOW_BREAKUP', 0);
define ('BBCODE_PARAGRAPH_ALLOW_INSIDE', 1);
define ('BBCODE_PARAGRAPH_BLOCK_ELEMENT', 2);

/**
 * BB code string parser class
 *
 * @package stringparser
 */
class StringParser_BBCode extends StringParser {
    /**
     * String parser mode
     *
     * The BBCode string parser works in search mode
     *
     * @access protected
     * @var int
     * @see STRINGPARSER_MODE_SEARCH, STRINGPARSER_MODE_LOOP
     */
    var $_parserMode = STRINGPARSER_MODE_SEARCH;
    
    /**
     * Defined BB Codes
     *
     * The registered BB codes
     *
     * @access protected
     * @var array
     */
    var $_codes = array ();
    
    /**
     * Registered parsers
     *
     * @access protected
     * @var array
     */
    var $_parsers = array ();
    
    /**
     * Defined maximum occurrences
     *
     * @access protected
     * @var array
     */
    var $_maxOccurrences = array ();
    
    /**
     * Root content type
     *
     * @access protected
     * @var string
     */
    var $_rootContentType = 'block';
    
    /**
     * Do not output but return the tree
     *
     * @access protected
     * @var bool
     */
    var $_noOutput = false;
    
    /**
     * Global setting: case sensitive
     *
     * @access protected
     * @var bool
     */
    var $_caseSensitive = true;
    
    /**
     * Root paragraph handling enabled
     *
     * @access protected
     * @var bool
     */
    var $_rootParagraphHandling = false;
    
    /**
     * Paragraph handling parameters
     * @access protected
     * @var array
     */
    var $_paragraphHandling = array (
        'detect_string' => "\n\n",
        'start_tag' => '<p>',
        'end_tag' => "</p>\n"
    );
    
    /**
     * Allow mixed attribute types (e.g. [code=bla attr=blub])
     * @access private
     * @var bool
     */
    var $_mixedAttributeTypes = false;
    
    /**
     * Whether to call validation function again (with $action == 'validate_auto') when closetag comes
     * @access protected
     * @var bool
     */
    var $_validateAgain = false;
    
    /**
     * Add a code
     *
     * @access public
     * @param string $name The name of the code
     * @param string $callback_type See documentation
     * @param string $callback_func The callback function to call
     * @param array $callback_params The callback parameters
     * @param string $content_type See documentation
     * @param array $allowed_within See documentation
     * @param array $not_allowed_within See documentation
     * @return bool
     */
    function addCode ($name, $callback_type, $callback_func, $callback_params, $content_type, $allowed_within, $not_allowed_within) {
        if (isset ($this->_codes[$name])) {
            return false; // already exists
        }
        if (!preg_match ('/^[a-zA-Z0-9*_!+-]+$/', $name)) {
            return false; // invalid
        }
        $this->_codes[$name] = array (
            'name' => $name,
            'callback_type' => $callback_type,
            'callback_func' => $callback_func,
            'callback_params' => $callback_params,
            'content_type' => $content_type,
            'allowed_within' => $allowed_within,
            'not_allowed_within' => $not_allowed_within,
            'flags' => array ()
        );
        return true;
    }
    
    /**
     * Remove a code
     *
     * @access public
     * @param $name The code to remove
     * @return bool
     */
    function removeCode ($name) {
        if (isset ($this->_codes[$name])) {
            unset ($this->_codes[$name]);
            return true;
        }
        return false;
    }
    
    /**
     * Remove all codes
     *
     * @access public
     */
    function removeAllCodes () {
        $this->_codes = array ();
    }
    
    /**
     * Set a code flag
     *
     * @access public
     * @param string $name The name of the code
     * @param string $flag The name of the flag to set
     * @param mixed $value The value of the flag to set
     * @return bool
     */
    function setCodeFlag ($name, $flag, $value) {
        if (!isset ($this->_codes[$name])) {
            return false;
        }
        $this->_codes[$name]['flags'][$flag] = $value;
        return true;
    }
    
    /**
     * Set occurrence type
     *
     * Example:
     *   $bbcode->setOccurrenceType ('url', 'link');
     *   $bbcode->setMaxOccurrences ('link', 4);
     * Would create the situation where a link may only occur four
     * times in the hole text.
     *
     * @access public
     * @param string $code The name of the code
     * @param string $type The name of the occurrence type to set
     * @return bool
     */
    function setOccurrenceType ($code, $type) {
        return $this->setCodeFlag ($code, 'occurrence_type', $type);
    }
    
    /**
     * Set maximum number of occurrences
     *
     * @access public
     * @param string $type The name of the occurrence type
     * @param int $count The maximum number of occurrences
     * @return bool
     */
    function setMaxOccurrences ($type, $count) {
        settype ($count, 'integer');
        if ($count < 0) { // sorry, does not make any sense
            return false;
        }
        $this->_maxOccurrences[$type] = $count;
        return true;
    }
    
    /**
     * Add a parser
     *
     * @access public
     * @param string $type The content type for which the parser is to add
     * @param mixed $parser The function to call
     * @return bool
     */
    function addParser ($type, $parser) {
        if (is_array ($type)) {
            foreach ($type as $t) {
                $this->addParser ($t, $parser);
            }
            return true;
        }
        if (!isset ($this->_parsers[$type])) {
            $this->_parsers[$type] = array ();
        }
        $this->_parsers[$type][] = $parser;
        return true;
    }
    
    /**
     * Set root content type
     *
     * @access public
     * @param string $content_type The new root content type
     */
    function setRootContentType ($content_type) {
        $this->_rootContentType = $content_type;
    }
    
    /**
     * Set paragraph handling on root element
     *
     * @access public
     * @param bool $enabled The new status of paragraph handling on root element
     */
    function setRootParagraphHandling ($enabled) {
        $this->_rootParagraphHandling = (bool)$enabled;
    }
    
    /**
     * Set paragraph handling parameters
     *
     * @access public
     * @param string $detect_string The string to detect
     * @param string $start_tag The replacement for the start tag (e.g. <p>)
     * @param string $end_tag The replacement for the start tag (e.g. </p>)
     */
    function setParagraphHandlingParameters ($detect_string, $start_tag, $end_tag) {
        $this->_paragraphHandling = array (
            'detect_string' => $detect_string,
            'start_tag' => $start_tag,
            'end_tag' => $end_tag
        );
    }
    
    /**
     * Set global case sensitive flag
     *
     * If this is set to true, the class normally is case sensitive, but
     * the case_sensitive code flag may override this for a single code.
     *
     * If this is set to false, all codes are case insensitive.
     *
     * @access public
     * @param bool $caseSensitive
     */
    function setGlobalCaseSensitive ($caseSensitive) {
        $this->_caseSensitive = (bool)$caseSensitive;
    }
    
    /**
     * Get global case sensitive flag
     *
     * @access public
     * @return bool
     */
    function globalCaseSensitive () {
        return $this->_caseSensitive;
    }
    
    /**
     * Set mixed attribute types flag
     *
     * If set, [code=val1 attr=val2] will cause 2 attributes to be parsed:
     * 'default' will have value 'val1', 'attr' will have value 'val2'.
     * If not set, only one attribute 'default' will have the value
     * 'val1 attr=val2' (the default and original behaviour)
     *
     * @access public
     * @param bool $mixedAttributeTypes
     */
    function setMixedAttributeTypes ($mixedAttributeTypes) {
        $this->_mixedAttributeTypes = (bool)$mixedAttributeTypes;
    }
    
    /**
     * Get mixed attribute types flag
     *
     * @access public
     * @return bool
     */
    function mixedAttributeTypes () {
        return $this->_mixedAttributeTypes;
    }
    
    /**
     * Set validate again flag
     *
     * If this is set to true, the class calls the validation function
     * again with $action == 'validate_again' when closetag comes.
     *
     * @access public
     * @param bool $validateAgain
     */
    function setValidateAgain ($validateAgain) {
        $this->_validateAgain = (bool)$validateAgain;
    }
    
    /**
     * Get validate again flag
     *
     * @access public
     * @return bool
     */
    function validateAgain () {
        return $this->_validateAgain;
    }
    
    /**
     * Get a code flag
     *
     * @access public
     * @param string $name The name of the code
     * @param string $flag The name of the flag to get
     * @param string $type The type of the return value
     * @param mixed $default The default return value
     * @return bool
     */
    function getCodeFlag ($name, $flag, $type = 'mixed', $default = null) {
        if (!isset ($this->_codes[$name])) {
            return $default;
        }
        if (!array_key_exists ($flag, $this->_codes[$name]['flags'])) {
            return $default;
        }
        $return = $this->_codes[$name]['flags'][$flag];
        if ($type != 'mixed') {
            settype ($return, $type);
        }
        return $return;
    }
    
    /**
     * Set a specific status
     * @access protected
     */
    function _setStatus ($status) {
        switch ($status) {
            case 0:
                $this->_charactersSearch = array ('[/', '[');
                $this->_status = $status;
                break;
            case 1:
                $this->_charactersSearch = array (']', ' = "', '="', ' = \'', '=\'', ' = ', '=', ': ', ':', ' ');
                $this->_status = $status;
                break;
            case 2:
                $this->_charactersSearch = array (']');
                $this->_status = $status;
                $this->_savedName = '';
                break;
            case 3:
                if ($this->_quoting !== null) {
                    if ($this->_mixedAttributeTypes) {
                        $this->_charactersSearch = array ('\\\\', '\\'.$this->_quoting, $this->_quoting.' ', $this->_quoting.']', $this->_quoting);
                    } else {
                        $this->_charactersSearch = array ('\\\\', '\\'.$this->_quoting, $this->_quoting.']', $this->_quoting);
                    }
                    $this->_status = $status;
                    break;
                }
                if ($this->_mixedAttributeTypes) {
                    $this->_charactersSearch = array (' ', ']');
                } else {
                    $this->_charactersSearch = array (']');
                }
                $this->_status = $status;
                break;
            case 4:
                $this->_charactersSearch = array (' ', ']', '="', '=\'', '=');
                $this->_status = $status;
                $this->_savedName = '';
                $this->_savedValue = '';
                break;
            case 5:
                if ($this->_quoting !== null) {
                    $this->_charactersSearch = array ('\\\\', '\\'.$this->_quoting, $this->_quoting.' ', $this->_quoting.']', $this->_quoting);
                } else {
                    $this->_charactersSearch = array (' ', ']');
                }
                $this->_status = $status;
                $this->_savedValue = '';
                break;
            case 7:
                $this->_charactersSearch = array ('[/'.$this->_topNode ('name').']');
                if (!$this->_topNode ('getFlag', 'case_sensitive', 'boolean', true) || !$this->_caseSensitive) {
                    $this->_charactersSearch[] = '[/';
                }
                $this->_status = $status;
                break;
            default:
                return false;
        }
        return true;
    }
    
    /**
     * Abstract method Append text depending on current status
     * @access protected
     * @param string $text The text to append
     * @return bool On success, the function returns true, else false
     */
    function _appendText ($text) {
        if (!strlen ($text)) {
            return true;
        }
        switch ($this->_status) {
            case 0:
            case 7:
                return $this->_appendToLastTextChild ($text);
            case 1:
                return $this->_topNode ('appendToName', $text);
            case 2:
            case 4:
                $this->_savedName .= $text;
                return true;
            case 3:
                return $this->_topNode ('appendToAttribute', 'default', $text);
            case 5:
                $this->_savedValue .= $text;
                return true;
            default:
                return false;
        }
    }
    
    /**
     * Restart parsing after current block
     *
     * To achieve this the current top stack object is removed from the
     * tree. Then the current item
     *
     * @access protected
     * @return bool
     */
    function _reparseAfterCurrentBlock () {
        if ($this->_status == 2) {
            // this status will *never* call _reparseAfterCurrentBlock itself
            // so this is called if the loop ends
            // therefore, just add the [/ to the text
            
            // _savedName should be empty but just in case
            $this->_cpos -= strlen ($this->_savedName);
            $this->_savedName = '';
            $this->_status = 0;
            $this->_appendText ('[/');
            return true;
        } else {
            return parent::_reparseAfterCurrentBlock ();
        }
    }
    
    /**
     * Apply parsers
     */
    function _applyParsers ($type, $text) {
        if (!isset ($this->_parsers[$type])) {
            return $text;
        }
        foreach ($this->_parsers[$type] as $parser) {
            if (is_callable ($parser)) {
                $ntext = call_user_func ($parser, $text);
                if (is_string ($ntext)) {
                    $text = $ntext;
                }
            }
        }
        return $text;
    }
    
    /**
     * Handle status
     * @access protected
     * @param int $status The current status
     * @param string $needle The needle that was found
     * @return bool
     */
    function _handleStatus ($status, $needle) {
        switch ($status) {
            case 0: // NORMAL TEXT
                if ($needle != '[' && $needle != '[/') {
                    $this->_appendText ($needle);
                    return true;
                }
                if ($needle == '[') {
                    $node = new StringParser_BBCode_Node_Element ($this->_cpos);
                    $res = $this->_pushNode ($node);
                    if (!$res) {
                        return false;
                    }
                    $this->_setStatus (1);
                } else if ($needle == '[/') {
                    if (count ($this->_stack) <= 1) {
                        $this->_appendText ($needle);
                        return true;
                    }
                    $this->_setStatus (2);
                }
                break;
            case 1: // OPEN TAG
                if ($needle == ']') {
                    return $this->_openElement (0);
                } else if (trim ($needle) == ':' || trim ($needle) == '=') {
                    $this->_quoting = null;
                    $this->_setStatus (3); // default value parser
                    break;
                } else if (trim ($needle) == '="' || trim ($needle) == '= "' || trim ($needle) == '=\'' || trim ($needle) == '= \'') {
                    $this->_quoting = substr (trim ($needle), -1);
                    $this->_setStatus (3); // default value parser with quotation
                    break;
                } else if ($needle == ' ') {
                    $this->_setStatus (4); // attribute parser
                    break;
                } else {
                    $this->_appendText ($needle);
                    return true;
                }
                // break not necessary because every if clause contains return
            case 2: // CLOSE TAG
                if ($needle != ']') {
                    $this->_appendText ($needle);
                    return true;
                }
                $closecount = 0;
                if (!$this->_isCloseable ($this->_savedName, $closecount)) {
                    $this->_setStatus (0);
                    $this->_appendText ('[/'.$this->_savedName.$needle);
                    return true;
                }
                // this validates the code(s) to be closed after the content tree of
                // that code(s) are built - if the second validation fails, we will have
                // to reparse. note that as _reparseAfterCurrentBlock will not work correctly
                // if we're in $status == 2, we will have to set our status to 0 manually
                if (!$this->_validateCloseTags ($closecount)) {
                    $this->_setStatus (0);
                    return $this->_reparseAfterCurrentBlock ();
                }
                $this->_setStatus (0);
                for ($i = 0; $i < $closecount; $i++) {
                    if ($i == $closecount - 1) {
                        $this->_topNode ('setHadCloseTag');
                    }
                    if (!$this->_popNode ()) {
                        return false;
                    }
                }
                break;
            case 3: // DEFAULT ATTRIBUTE
                if ($this->_quoting !== null) {
                    if ($needle == '\\\\') {
                        $this->_appendText ('\\');
                        return true;
                    } else if ($needle == '\\'.$this->_quoting) {
                        $this->_appendText ($this->_quoting);
                        return true;
                    } else if ($needle == $this->_quoting.' ') {
                        $this->_setStatus (4);
                        return true;
                    } else if ($needle == $this->_quoting.']') {
                        return $this->_openElement (2);
                    } else if ($needle == $this->_quoting) {
                        // can't be, only ']' and ' ' allowed after quoting char
                        return $this->_reparseAfterCurrentBlock ();
                    } else {
                        $this->_appendText ($needle);
                        return true;
                    }
                } else {
                    if ($needle == ' ') {
                        $this->_setStatus (4);
                        return true;
                    } else if ($needle == ']') {
                        return $this->_openElement (2);
                    } else {
                        $this->_appendText ($needle);
                        return true;
                    }
                }
                // break not needed because every if clause contains return!
            case 4: // ATTRIBUTE NAME
                if ($needle == ' ') {
                    if (strlen ($this->_savedName)) {
                        $this->_topNode ('setAttribute', $this->_savedName, true);
                    }
                    // just ignore and continue in same mode
                    $this->_setStatus (4); // reset parameters
                    return true;
                } else if ($needle == ']') {
                    if (strlen ($this->_savedName)) {
                        $this->_topNode ('setAttribute', $this->_savedName, true);
                    }
                    return $this->_openElement (2);
                } else if ($needle == '=') {
                    $this->_quoting = null;
                    $this->_setStatus (5);
                    return true;
                } else if ($needle == '="') {
                    $this->_quoting = '"';
                    $this->_setStatus (5);
                    return true;
                } else if ($needle == '=\'') {
                    $this->_quoting = '\'';
                    $this->_setStatus (5);
                    return true;
                } else {
                    $this->_appendText ($needle);
                    return true;
                }
                // break not needed because every if clause contains return!
            case 5: // ATTRIBUTE VALUE
                if ($this->_quoting !== null) {
                    if ($needle == '\\\\') {
                        $this->_appendText ('\\');
                        return true;
                    } else if ($needle == '\\'.$this->_quoting) {
                        $this->_appendText ($this->_quoting);
                        return true;
                    } else if ($needle == $this->_quoting.' ') {
                        $this->_topNode ('setAttribute', $this->_savedName, $this->_savedValue);
                        $this->_setStatus (4);
                        return true;
                    } else if ($needle == $this->_quoting.']') {
                        $this->_topNode ('setAttribute', $this->_savedName, $this->_savedValue);
                        return $this->_openElement (2);
                    } else if ($needle == $this->_quoting) {
                        // can't be, only ']' and ' ' allowed after quoting char
                        return $this->_reparseAfterCurrentBlock ();
                    } else {
                        $this->_appendText ($needle);
                        return true;
                    }
                } else {
                    if ($needle == ' ') {
                        $this->_topNode ('setAttribute', $this->_savedName, $this->_savedValue);
                        $this->_setStatus (4);
                        return true;
                    } else if ($needle == ']') {
                        $this->_topNode ('setAttribute', $this->_savedName, $this->_savedValue);
                        return $this->_openElement (2);
                    } else {
                        $this->_appendText ($needle);
                        return true;
                    }
                }
                // break not needed because every if clause contains return!
            case 7:
                if ($needle == '[/') {
                    // this was case insensitive match
                    if (strtolower (substr ($this->_text, $this->_cpos + strlen ($needle), strlen ($this->_topNode ('name')) + 1)) == strtolower ($this->_topNode ('name').']')) {
                        // this matched
                        $this->_cpos += strlen ($this->_topNode ('name')) + 1;
                    } else {
                        // it didn't match
                        $this->_appendText ($needle);
                        return true;
                    }
                }
                $closecount = $this->_savedCloseCount;
                if (!$this->_topNode ('validate')) {
                    return $this->_reparseAfterCurrentBlock ();
                }
                // do we have to close subnodes?
                if ($closecount) {
                    // get top node
                    $mynode =& $this->_stack[count ($this->_stack)-1];
                    // close necessary nodes
                    for ($i = 0; $i <= $closecount; $i++) {
                        if (!$this->_popNode ()) {
                            return false;
                        }
                    }
                    if (!$this->_pushNode ($mynode)) {
                        return false;
                    }
                }
                $this->_setStatus (0);
                $this->_popNode ();
                return true;
            default: 
                return false;
        }
        return true;
    }
    
    /**
     * Open the next element
     *
     * @access protected
     * @return bool
     */
    function _openElement ($type = 0) {
        $name = $this->_getCanonicalName ($this->_topNode ('name'));
        if ($name === false) {
            return $this->_reparseAfterCurrentBlock ();
        }
        $occ_type = $this->getCodeFlag ($name, 'occurrence_type', 'string');
        if ($occ_type !== null && isset ($this->_maxOccurrences[$occ_type])) {
            $max_occs = $this->_maxOccurrences[$occ_type];
            $occs = $this->_root->getNodeCountByCriterium ('flag:occurrence_type', $occ_type);
            if ($occs >= $max_occs) {
                return $this->_reparseAfterCurrentBlock ();
            }
        }
        $closecount = 0;
        $this->_topNode ('setCodeInfo', $this->_codes[$name]);
        if (!$this->_isOpenable ($name, $closecount)) {
            return $this->_reparseAfterCurrentBlock ();
        }
        $this->_setStatus (0);
        switch ($type) {
        case 0:
            $cond = $this->_isUseContent ($this->_stack[count($this->_stack)-1], false);
            break;
        case 1:
            $cond = $this->_isUseContent ($this->_stack[count($this->_stack)-1], true);
            break;
        case 2:
            $cond = $this->_isUseContent ($this->_stack[count($this->_stack)-1], true);
            break;
        default:
            $cond = false;
            break;
        }
        if ($cond) {
            $this->_savedCloseCount = $closecount;
            $this->_setStatus (7);
            return true;
        }
        if (!$this->_topNode ('validate')) {
            return $this->_reparseAfterCurrentBlock ();
        }
        // do we have to close subnodes?
        if ($closecount) {
            // get top node
            $mynode =& $this->_stack[count ($this->_stack)-1];
            // close necessary nodes
            for ($i = 0; $i <= $closecount; $i++) {
                if (!$this->_popNode ()) {
                    return false;
                }
            }
            if (!$this->_pushNode ($mynode)) {
                return false;
            }
        }
        
        if ($this->_codes[$name]['callback_type'] == 'simple_replace_single' || $this->_codes[$name]['callback_type'] == 'callback_replace_single') {
            if (!$this->_popNode ())  {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Is a node closeable?
     *
     * @access protected
     * @return bool
     */
    function _isCloseable ($name, &$closecount) {
        $node =& $this->_findNamedNode ($name, false);
        if ($node === false) {
            return false;
        }
        $scount = count ($this->_stack);
        for ($i = $scount - 1; $i > 0; $i--) {
            $closecount++;
            if ($this->_stack[$i]->equals ($node)) {
                return true;
            }
            if ($this->_stack[$i]->getFlag ('closetag', 'integer', BBCODE_CLOSETAG_IMPLICIT) == BBCODE_CLOSETAG_MUSTEXIST) {
                return false;
            }
        }
        return false;
    }
    
    /**
     * Revalidate codes when close tags appear
     *
     * @access protected
     * @return bool
     */
    function _validateCloseTags ($closecount) {
        $scount = count ($this->_stack);
        for ($i = $scount - 1; $i >= $scount - $closecount; $i--) {
            if ($this->_validateAgain) {
                if (!$this->_stack[$i]->validate ('validate_again')) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Is a node openable?
     *
     * @access protected
     * @return bool
     */
    function _isOpenable ($name, &$closecount) {
        if (!isset ($this->_codes[$name])) {
            return false;
        }
        
        $closecount = 0;
        
        $allowed_within = $this->_codes[$name]['allowed_within'];
        $not_allowed_within = $this->_codes[$name]['not_allowed_within'];
        
        $scount = count ($this->_stack);
        if ($scount == 2) { // top level element
            if (!in_array ($this->_rootContentType, $allowed_within)) {
                return false;
            }
        } else {
            if (!in_array ($this->_stack[$scount-2]->_codeInfo['content_type'], $allowed_within)) {
                return $this->_isOpenableWithClose ($name, $closecount);
            }
        }
        
        for ($i = 1; $i < $scount - 1; $i++) {
            if (in_array ($this->_stack[$i]->_codeInfo['content_type'], $not_allowed_within)) {
                return $this->_isOpenableWithClose ($name, $closecount);
            }
        }
        
        return true;
    }
    
    /**
     * Is a node openable by closing other nodes?
     *
     * @access protected
     * @return bool
     */
    function _isOpenableWithClose ($name, &$closecount) {
        $tnname = $this->_getCanonicalName ($this->_topNode ('name'));
        if (!in_array ($this->getCodeFlag ($tnname, 'closetag', 'integer', BBCODE_CLOSETAG_IMPLICIT), array (BBCODE_CLOSETAG_FORBIDDEN, BBCODE_CLOSETAG_OPTIONAL))) {
            return false;
        }
        $node =& $this->_findNamedNode ($name, true);
        if ($node === false) {
            return false;
        }
        $scount = count ($this->_stack);
        if ($scount < 3) {
            return false;
        }
        for ($i = $scount - 2; $i > 0; $i--) {
            $closecount++;
            if ($this->_stack[$i]->equals ($node)) {
                return true;
            }
            if (in_array ($this->_stack[$i]->getFlag ('closetag', 'integer', BBCODE_CLOSETAG_IMPLICIT), array (BBCODE_CLOSETAG_IMPLICIT_ON_CLOSE_ONLY, BBCODE_CLOSETAG_MUSTEXIST))) {
                return false;
            }
            if ($this->_validateAgain) {
                if (!$this->_stack[$i]->validate ('validate_again')) {
                    return false;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Abstract method: Close remaining blocks
     * @access protected
     */
    function _closeRemainingBlocks () {
        // everything closed
        if (count ($this->_stack) == 1) {
            return true;
        }
        // not everything close
        if ($this->strict) {
            return false;
        }
        while (count ($this->_stack) > 1) {
            if ($this->_topNode ('getFlag', 'closetag', 'integer', BBCODE_CLOSETAG_IMPLICIT) == BBCODE_CLOSETAG_MUSTEXIST) {
                return false; // sorry
            }
            $res = $this->_popNode ();
            if (!$res) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Find a node with a specific name in stack
     *
     * @access protected
     * @return mixed
     */
    function &_findNamedNode ($name, $searchdeeper = false) {
        $lname = $this->_getCanonicalName ($name);
        $case_sensitive = $this->_caseSensitive && $this->getCodeFlag ($lname, 'case_sensitive', 'boolean', true);
        if ($case_sensitive) {
            $name = strtolower ($name);
        }
        $scount = count ($this->_stack);
        if ($searchdeeper) {
            $scount--;
        }
        for ($i = $scount - 1; $i > 0; $i--) {
            if (!$case_sensitive) {
                $cmp_name = strtolower ($this->_stack[$i]->name ());
            } else {
                $cmp_name = $this->_stack[$i]->name ();
            }
            if ($cmp_name == $lname) {
                return $this->_stack[$i];
            }
        }
        $result = false;
        return $result;
    }
    
    /**
     * Abstract method: Output tree
     * @access protected
     * @return bool
     */
    function _outputTree () {
        if ($this->_noOutput) {
            return true;
        }
        $output = $this->_outputNode ($this->_root);
        if (is_string ($output)) {
            $this->_output = $this->_applyPostfilters ($output);
            unset ($output);
            return true;
        }
        
        return false;
    }
    
    /**
     * Output a node
     * @access protected
     * @return bool
     */
    function _outputNode (&$node) {
        $output = '';
        if ($node->_type == STRINGPARSER_BBCODE_NODE_PARAGRAPH || $node->_type == STRINGPARSER_BBCODE_NODE_ELEMENT || $node->_type == STRINGPARSER_NODE_ROOT) {
            $ccount = count ($node->_children);
            for ($i = 0; $i < $ccount; $i++) {
                $suboutput = $this->_outputNode ($node->_children[$i]);
                if (!is_string ($suboutput)) {
                    return false;
                }
                $output .= $suboutput;
            }
            if ($node->_type == STRINGPARSER_BBCODE_NODE_PARAGRAPH) {
                return $this->_paragraphHandling['start_tag'].$output.$this->_paragraphHandling['end_tag'];
            }
            if ($node->_type == STRINGPARSER_BBCODE_NODE_ELEMENT) {
                return $node->getReplacement ($output);
            }
            return $output;
        } else if ($node->_type == STRINGPARSER_NODE_TEXT) {
            $output = $node->content;
            $before = '';
            $after = '';
            $ol = strlen ($output);
            switch ($node->getFlag ('newlinemode.begin', 'integer', BBCODE_NEWLINE_PARSE)) {
            case BBCODE_NEWLINE_IGNORE:
                if ($ol && $output{0} == "\n") {
                    $before = "\n";
                }
                // don't break!
            case BBCODE_NEWLINE_DROP:
                if ($ol && $output{0} == "\n") {
                    $output = substr ($output, 1);
                    $ol--;
                }
                break;
            }
            switch ($node->getFlag ('newlinemode.end', 'integer', BBCODE_NEWLINE_PARSE)) {
            case BBCODE_NEWLINE_IGNORE:
                if ($ol && $output{$ol-1} == "\n") {
                    $after = "\n";
                }
                // don't break!
            case BBCODE_NEWLINE_DROP:
                if ($ol && $output{$ol-1} == "\n") {
                    $output = substr ($output, 0, -1);
                    $ol--;
                }
                break;
            }
            // can't do anything
            if ($node->_parent === null) {
                return $before.$output.$after;
            }
            if ($node->_parent->_type == STRINGPARSER_BBCODE_NODE_PARAGRAPH)  {
                $parent =& $node->_parent;
                unset ($node);
                $node =& $parent;
                unset ($parent);
                // if no parent for this paragraph
                if ($node->_parent === null) {
                    return $before.$output.$after;
                }
            }
            if ($node->_parent->_type == STRINGPARSER_NODE_ROOT) {
                return $before.$this->_applyParsers ($this->_rootContentType, $output).$after;
            }
            if ($node->_parent->_type == STRINGPARSER_BBCODE_NODE_ELEMENT) {
                return $before.$this->_applyParsers ($node->_parent->_codeInfo['content_type'], $output).$after;
            }
            return $before.$output.$after;
        }
    }
    
    /**
     * Abstract method: Manipulate the tree
     * @access protected
     * @return bool
     */
    function _modifyTree () {
        // first pass: try to do newline handling
        $nodes =& $this->_root->getNodesByCriterium ('needsTextNodeModification', true);
        $nodes_count = count ($nodes);
        for ($i = 0; $i < $nodes_count; $i++) {
            $v = $nodes[$i]->getFlag ('opentag.before.newline', 'integer', BBCODE_NEWLINE_PARSE);
            if ($v != BBCODE_NEWLINE_PARSE) {
                $n =& $nodes[$i]->findPrevAdjentTextNode ();
                if (!is_null ($n)) {
                    $n->setFlag ('newlinemode.end', $v);
                }
                unset ($n);
            }
            $v = $nodes[$i]->getFlag ('opentag.after.newline', 'integer', BBCODE_NEWLINE_PARSE);
            if ($v != BBCODE_NEWLINE_PARSE) {
                $n =& $nodes[$i]->firstChildIfText ();
                if (!is_null ($n)) {
                    $n->setFlag ('newlinemode.begin', $v);
                }
                unset ($n);
            }
            $v = $nodes[$i]->getFlag ('closetag.before.newline', 'integer', BBCODE_NEWLINE_PARSE);
            if ($v != BBCODE_NEWLINE_PARSE) {
                $n =& $nodes[$i]->lastChildIfText ();
                if (!is_null ($n)) {
                    $n->setFlag ('newlinemode.end', $v);
                }
                unset ($n);
            }
            $v = $nodes[$i]->getFlag ('closetag.after.newline', 'integer', BBCODE_NEWLINE_PARSE);
            if ($v != BBCODE_NEWLINE_PARSE) {
                $n =& $nodes[$i]->findNextAdjentTextNode ();
                if (!is_null ($n)) {
                    $n->setFlag ('newlinemode.begin', $v);
                }
                unset ($n);
            }
        }
        
        // second pass a: do paragraph handling on root element
        if ($this->_rootParagraphHandling) {
            $res = $this->_handleParagraphs ($this->_root);
            if (!$res) {
                return false;
            }
        }
        
        // second pass b: do paragraph handling on other elements
        unset ($nodes);
        $nodes =& $this->_root->getNodesByCriterium ('flag:paragraphs', true);
        $nodes_count = count ($nodes);
        for ($i = 0; $i < $nodes_count; $i++) {
            $res = $this->_handleParagraphs ($nodes[$i]);
            if (!$res) {
                return false;
            }
        }
        
        // second pass c: search for empty paragraph nodes and remove them
        unset ($nodes);
        $nodes =& $this->_root->getNodesByCriterium ('empty', true);
        $nodes_count = count ($nodes);
        if (isset ($parent)) {
            unset ($parent); $parent = null;
        }
        for ($i = 0; $i < $nodes_count; $i++) {
            if ($nodes[$i]->_type != STRINGPARSER_BBCODE_NODE_PARAGRAPH) {
                continue;
            }
            unset ($parent);
            $parent =& $nodes[$i]->_parent;
            $parent->removeChild ($nodes[$i], true);
        }
        
        return true;
    }
    
    /**
     * Handle paragraphs
     * @access protected
     * @param object $node The node to handle
     * @return bool
     */
    function _handleParagraphs (&$node) {
        // if this node is already a subnode of a paragraph node, do NOT 
        // do paragraph handling on this node!
        if ($this->_hasParagraphAncestor ($node)) {
            return true;
        }
        $dest_nodes = array ();
        $last_node_was_paragraph = false;
        $prevtype = STRINGPARSER_NODE_TEXT;
        $paragraph = null;
        while (count ($node->_children)) {
            $mynode =& $node->_children[0];
            $node->removeChild ($mynode);
            $subprevtype = $prevtype;
            $sub_nodes =& $this->_breakupNodeByParagraphs ($mynode);
            for ($i = 0; $i < count ($sub_nodes); $i++) {
                if (!$last_node_was_paragraph ||  ($prevtype == $sub_nodes[$i]->_type && ($i != 0 || $prevtype != STRINGPARSER_BBCODE_NODE_ELEMENT))) {
                    unset ($paragraph);
                    $paragraph = new StringParser_BBCode_Node_Paragraph ();
                }
                $prevtype = $sub_nodes[$i]->_type;
                if ($sub_nodes[$i]->_type != STRINGPARSER_BBCODE_NODE_ELEMENT || $sub_nodes[$i]->getFlag ('paragraph_type', 'integer', BBCODE_PARAGRAPH_ALLOW_BREAKUP) != BBCODE_PARAGRAPH_BLOCK_ELEMENT) {
                    $paragraph->appendChild ($sub_nodes[$i]);
                    $dest_nodes[] =& $paragraph;
                    $last_node_was_paragraph = true;
                } else {
                    $dest_nodes[] =& $sub_nodes[$i];
                    $last_onde_was_paragraph = false;
                    unset ($paragraph);
                    $paragraph = new StringParser_BBCode_Node_Paragraph ();
                }
            }
        }
        $count = count ($dest_nodes);
        for ($i = 0; $i < $count; $i++) {
            $node->appendChild ($dest_nodes[$i]);
        }
        unset ($dest_nodes);
        unset ($paragraph);
        return true;
    }
    
    /**
     * Search for a paragraph node in tree in upward direction
     * @access protected
     * @param object $node The node to analyze
     * @return bool
     */
    function _hasParagraphAncestor (&$node) {
        if ($node->_parent === null) {
            return false;
        }
        $parent =& $node->_parent;
        if ($parent->_type == STRINGPARSER_BBCODE_NODE_PARAGRAPH) {
            return true;
        }
        return $this->_hasParagraphAncestor ($parent);
    }
    
    /**
     * Break up nodes
     * @access protected
     * @param object $node The node to break up
     * @return array
     */
    function &_breakupNodeByParagraphs (&$node) {
        $detect_string = $this->_paragraphHandling['detect_string'];
        $dest_nodes = array ();
        // text node => no problem
        if ($node->_type == STRINGPARSER_NODE_TEXT) {
            $cpos = 0;
            while (($npos = strpos ($node->content, $detect_string, $cpos)) !== false) {
                $subnode = new StringParser_Node_Text (substr ($node->content, $cpos, $npos - $cpos), $node->occurredAt + $cpos);
                // copy flags
                foreach ($node->_flags as $flag => $value) {
                    if ($flag == 'newlinemode.begin') {
                        if ($cpos == 0) {
                            $subnode->setFlag ($flag, $value);
                        }
                    } else if ($flag == 'newlinemode.end') {
                        // do nothing
                    } else {
                        $subnode->setFlag ($flag, $value);
                    }
                }
                $dest_nodes[] =& $subnode;
                unset ($subnode);
                $cpos = $npos + strlen ($detect_string);
            }
            $subnode = new StringParser_Node_Text (substr ($node->content, $cpos), $node->occurredAt + $cpos);
            if ($cpos == 0) {
                $value = $node->getFlag ('newlinemode.begin', 'integer', null);
                if ($value !== null) {
                    $subnode->setFlag ('newlinemode.begin', $value);
                }
            }
            $value = $node->getFlag ('newlinemode.end', 'integer', null);
            if ($value !== null) {
                $subnode->setFlag ('newlinemode.end', $value);
            }
            $dest_nodes[] =& $subnode;
            unset ($subnode);
            return $dest_nodes;
        }
        // not a text node or an element node => no way
        if ($node->_type != STRINGPARSER_BBCODE_NODE_ELEMENT) {
            $dest_nodes[] =& $node;
            return $dest_nodes;
        }
        if ($node->getFlag ('paragraph_type', 'integer', BBCODE_PARAGRAPH_ALLOW_BREAKUP) != BBCODE_PARAGRAPH_ALLOW_BREAKUP || !count ($node->_children)) {
            $dest_nodes[] =& $node;
            return $dest_nodes;
        }
        $dest_node =& $node->duplicate ();
        $nodecount = count ($node->_children);
        // now this node allows breakup - do it
        for ($i = 0; $i < $nodecount; $i++) {
            $firstnode =& $node->_children[0];
            $node->removeChild ($firstnode);
            $sub_nodes =& $this->_breakupNodeByParagraphs ($firstnode);
            for ($j = 0; $j < count ($sub_nodes); $j++) {
                if ($j != 0) {
                    $dest_nodes[] =& $dest_node;
                    unset ($dest_node);
                    $dest_node =& $node->duplicate ();
                }
                $dest_node->appendChild ($sub_nodes[$j]);
            }
            unset ($sub_nodes);
        }
        $dest_nodes[] =& $dest_node;
        return $dest_nodes;
    }
    
    /**
     * Is this node a usecontent node
     * @access protected
     * @param object $node The node to check
     * @param bool $check_attrs Also check whether 'usecontent?'-attributes exist
     * @return bool
     */
    function _isUseContent (&$node, $check_attrs = false) {
        $name = $this->_getCanonicalName ($node->name ());
        // this should NOT happen
        if ($name === false) {
            return false;
        }
        if ($this->_codes[$name]['callback_type'] == 'usecontent') {
            return true;
        }
        $result = false;
        if ($this->_codes[$name]['callback_type'] == 'callback_replace?') {
            $result = true;
        } else if ($this->_codes[$name]['callback_type'] != 'usecontent?') {
            return false;
        }
        if ($check_attrs === false) {
            return !$result;
        }
        $attributes = array_keys ($this->_topNodeVar ('_attributes'));
        $p = @$this->_codes[$name]['callback_params']['usecontent_param'];
        if (is_array ($p)) {
            foreach ($p as $param) {
                if (in_array ($param, $attributes)) {
                    return $result;
                }
            }
        } else {
            if (in_array ($p, $attributes)) {
                return $result;
            }
        }
        return !$result;
    }

    /**
    * Get canonical name of a code
    *
    * @access protected
    * @param string $name
    * @return string
    */
    function _getCanonicalName ($name) {
        if (isset ($this->_codes[$name])) {
            return $name;
        }
        $found = false;
        // try to find the code in the code list
        foreach (array_keys ($this->_codes) as $rname) {
            // match
            if (strtolower ($rname) == strtolower ($name)) {
                $found = $rname;
                break;
            }
        }
        if ($found === false || ($this->_caseSensitive && $this->getCodeFlag ($found, 'case_sensitive', 'boolean', true))) {
            return false;
        }
        return $rname;
    }
}

/**
 * Node type: BBCode Element node
 * @see StringParser_BBCode_Node_Element::_type
 */
define ('STRINGPARSER_BBCODE_NODE_ELEMENT', 32);

/**
 * Node type: BBCode Paragraph node
 * @see StringParser_BBCode_Node_Paragraph::_type
 */
define ('STRINGPARSER_BBCODE_NODE_PARAGRAPH', 33);


/**
 * BBCode String parser paragraph node class
 *
 * @package stringparser
 */
class StringParser_BBCode_Node_Paragraph extends StringParser_Node {
    /**
     * The type of this node.
     * 
     * This node is a bbcode paragraph node.
     *
     * @access protected
     * @var int
     * @see STRINGPARSER_BBCODE_NODE_PARAGRAPH
     */
    var $_type = STRINGPARSER_BBCODE_NODE_PARAGRAPH;
    
    /**
     * Determines whether a criterium matches this node
     *
     * @access public
     * @param string $criterium The criterium that is to be checked
     * @param mixed $value The value that is to be compared
     * @return bool True if this node matches that criterium
     */
    function matchesCriterium ($criterium, $value) {
        if ($criterium == 'empty') {
            if (!count ($this->_children)) {
                return true;
            }
            if (count ($this->_children) > 1) {
                return false;
            }
            if ($this->_children[0]->_type != STRINGPARSER_NODE_TEXT) {
                return false;
            }
            if (!strlen ($this->_children[0]->content)) {
                return true;
            }
            if (strlen ($this->_children[0]->content) > 2) {
                return false;
            }
            $f_begin = $this->_children[0]->getFlag ('newlinemode.begin', 'integer', BBCODE_NEWLINE_PARSE);
            $f_end = $this->_children[0]->getFlag ('newlinemode.end', 'integer', BBCODE_NEWLINE_PARSE);
            $content = $this->_children[0]->content;
            if ($f_begin != BBCODE_NEWLINE_PARSE && $content{0} == "\n") {
                $content = substr ($content, 1);
            }
            if ($f_end != BBCODE_NEWLINE_PARSE && $content{strlen($content)-1} == "\n") {
                $content = substr ($content, 0, -1);
            }
            if (!strlen ($content)) {
                return true;
            }
            return false;
        }
    }
}

/**
 * BBCode String parser element node class
 *
 * @package stringparser
 */
class StringParser_BBCode_Node_Element extends StringParser_Node {
    /**
     * The type of this node.
     * 
     * This node is a bbcode element node.
     *
     * @access protected
     * @var int
     * @see STRINGPARSER_BBCODE_NODE_ELEMENT
     */
    var $_type = STRINGPARSER_BBCODE_NODE_ELEMENT;
    
    /**
     * Element name
     *
     * @access protected
     * @var string
     * @see StringParser_BBCode_Node_Element::name
     * @see StringParser_BBCode_Node_Element::setName
     * @see StringParser_BBCode_Node_Element::appendToName
     */
    var $_name = '';
    
    /**
     * Element flags
     * 
     * @access protected
     * @var array
     */
    var $_flags = array ();
    
    /**
     * Element attributes
     * 
     * @access protected
     * @var array
     */
    var $_attributes = array ();
    
    /**
     * Had a close tag
     *
     * @access protected
     * @var bool
     */
    var $_hadCloseTag = false;
    
    /**
     * Was processed by paragraph handling
     *
     * @access protected
     * @var bool
     */
    var $_paragraphHandled = false;
    
    //////////////////////////////////////////////////
    
    /**
     * Duplicate this node (but without children / parents)
     *
     * @access public
     * @return object
     */
    function &duplicate () {
        $newnode = new StringParser_BBCode_Node_Element ($this->occurredAt);
        $newnode->_name = $this->_name;
        $newnode->_flags = $this->_flags;
        $newnode->_attributes = $this->_attributes;
        $newnode->_hadCloseTag = $this->_hadCloseTag;
        $newnode->_paragraphHandled = $this->_paragraphHandled;
        $newnode->_codeInfo = $this->_codeInfo;
        return $newnode;
    }
    
    /**
     * Retreive name of this element
     *
     * @access public
     * @return string
     */
    function name () {
        return $this->_name;
    }
    
    /**
     * Set name of this element
     *
     * @access public
     * @param string $name The new name of the element
     */
    function setName ($name) {
        $this->_name = $name;
        return true;
    }
    
    /**
     * Append to name of this element
     *
     * @access public
     * @param string $chars The chars to append to the name of the element
     */
    function appendToName ($chars) {
        $this->_name .= $chars;
        return true;
    }
    
    /**
     * Append to attribute of this element
     *
     * @access public
     * @param string $name The name of the attribute
     * @param string $chars The chars to append to the attribute of the element
     */
    function appendToAttribute ($name, $chars) {
        if (!isset ($this->_attributes[$name])) {
            $this->_attributes[$name] = $chars;
            return true;
        }
        $this->_attributes[$name] .= $chars;
        return true;
    }
    
    /**
     * Set attribute
     *
     * @access public
     * @param string $name The name of the attribute
     * @param string $value The new value of the attribute
     */
    function setAttribute ($name, $value) {
        $this->_attributes[$name] = $value;
        return true;
    }
    
    /**
     * Set code info
     *
     * @access public
     * @param array $info The code info array
     */
    function setCodeInfo ($info) {
        $this->_codeInfo = $info;
        $this->_flags = $info['flags'];
        return true;
    }
    
    /**
     * Get attribute value
     *
     * @access public
     * @param string $name The name of the attribute
     */
    function attribute ($name) {
        if (!isset ($this->_attributes[$name])) {
            return null;
        }
        return $this->_attributes[$name];
    }
    
    /**
     * Set flag that this element had a close tag
     *
     * @access public
     */
    function setHadCloseTag () {
        $this->_hadCloseTag = true;
    }
    
    /**
     * Set flag that this element was already processed by paragraph handling
     *
     * @access public
     */
    function setParagraphHandled () {
        $this->_paragraphHandled = true;
    }
    
    /**
     * Get flag if this element was already processed by paragraph handling
     *
     * @access public
     * @return bool
     */
    function paragraphHandled () {
        return $this->_paragraphHandled;
    }
    
    /**
     * Get flag if this element had a close tag
     *
     * @access public
     * @return bool
     */
    function hadCloseTag () {
        return $this->_hadCloseTag;
    }
    
    /**
     * Determines whether a criterium matches this node
     *
     * @access public
     * @param string $criterium The criterium that is to be checked
     * @param mixed $value The value that is to be compared
     * @return bool True if this node matches that criterium
     */
    function matchesCriterium ($criterium, $value) {
        if ($criterium == 'tagName') {
            return ($value == $this->_name);
        }
        if ($criterium == 'needsTextNodeModification') {
            return (($this->getFlag ('opentag.before.newline', 'integer', BBCODE_NEWLINE_PARSE) != BBCODE_NEWLINE_PARSE || $this->getFlag ('opentag.after.newline', 'integer', BBCODE_NEWLINE_PARSE) != BBCODE_NEWLINE_PARSE || ($this->_hadCloseTag && ($this->getFlag ('closetag.before.newline', 'integer', BBCODE_NEWLINE_PARSE) != BBCODE_NEWLINE_PARSE || $this->getFlag ('closetag.after.newline', 'integer', BBCODE_NEWLINE_PARSE) != BBCODE_NEWLINE_PARSE))) == (bool)$value);
        }
        if (substr ($criterium, 0, 5) == 'flag:') {
            $criterium = substr ($criterium, 5);
            return ($this->getFlag ($criterium) == $value);
        }
        if (substr ($criterium, 0, 6) == '!flag:') {
            $criterium = substr ($criterium, 6);
            return ($this->getFlag ($criterium) != $value);
        }
        if (substr ($criterium, 0, 6) == 'flag=:') {
            $criterium = substr ($criterium, 6);
            return ($this->getFlag ($criterium) === $value);
        }
        if (substr ($criterium, 0, 7) == '!flag=:') {
            $criterium = substr ($criterium, 7);
            return ($this->getFlag ($criterium) !== $value);
        }
        return parent::matchesCriterium ($criterium, $value);
    }
    
    /**
     * Get first child if it is a text node
     *
     * @access public
     * @return mixed
     */
    function &firstChildIfText () {
        $ret =& $this->firstChild ();
        if (is_null ($ret)) {
            return $ret;
        }
        if ($ret->_type != STRINGPARSER_NODE_TEXT) {
            // DON'T DO $ret = null WITHOUT unset BEFORE!
            // ELSE WE WILL ERASE THE NODE ITSELF! EVIL!
            unset ($ret);
            $ret = null;
        }
        return $ret;
    }
    
    /**
     * Get last child if it is a text node AND if this element had a close tag
     *
     * @access public
     * @return mixed
     */
    function &lastChildIfText () {
        $ret =& $this->lastChild ();
        if (is_null ($ret)) {
            return $ret;
        }
        if ($ret->_type != STRINGPARSER_NODE_TEXT || !$this->_hadCloseTag) {
            // DON'T DO $ret = null WITHOUT unset BEFORE!
            // ELSE WE WILL ERASE THE NODE ITSELF! EVIL!
            if ($ret->_type != STRINGPARSER_NODE_TEXT && !$ret->hadCloseTag ()) {
                $ret2 =& $ret->_findPrevAdjentTextNodeHelper ();
                unset ($ret);
                $ret =& $ret2;
                unset ($ret2);
            } else {
                unset ($ret);
                $ret = null;
            }
        }
        return $ret;
    }
    
    /**
     * Find next adjent text node after close tag
     *
     * returns the node or null if none exists
     *
     * @access public
     * @return mixed
     */
    function &findNextAdjentTextNode () {
        $ret = null;
        if (is_null ($this->_parent)) {
            return $ret;
        }
        if (!$this->_hadCloseTag) {
            return $ret;
        }
        $ccount = count ($this->_parent->_children);
        $found = false;
        for ($i = 0; $i < $ccount; $i++) {
            if ($this->_parent->_children[$i]->equals ($this)) {
                $found = $i;
                break;
            }
        }
        if ($found === false) {
            return $ret;
        }
        if ($found < $ccount - 1) {
            if ($this->_parent->_children[$found+1]->_type == STRINGPARSER_NODE_TEXT) {
                return $this->_parent->_children[$found+1];
            }
            return $ret;
        }
        if ($this->_parent->_type == STRINGPARSER_BBCODE_NODE_ELEMENT && !$this->_parent->hadCloseTag ()) {
            $ret =& $this->_parent->findNextAdjentTextNode ();
            return $ret;
        }
        return $ret;
    }
    
    /**
     * Find previous adjent text node before open tag
     *
     * returns the node or null if none exists
     *
     * @access public
     * @return mixed
     */
    function &findPrevAdjentTextNode () {
        $ret = null;
        if (is_null ($this->_parent)) {
            return $ret;
        }
        $ccount = count ($this->_parent->_children);
        $found = false;
        for ($i = 0; $i < $ccount; $i++) {
            if ($this->_parent->_children[$i]->equals ($this)) {
                $found = $i;
                break;
            }
        }
        if ($found === false) {
            return $ret;
        }
        if ($found > 0) {
            if ($this->_parent->_children[$found-1]->_type == STRINGPARSER_NODE_TEXT) {
                return $this->_parent->_children[$found-1];
            }
            if (!$this->_parent->_children[$found-1]->hadCloseTag ()) {
                $ret =& $this->_parent->_children[$found-1]->_findPrevAdjentTextNodeHelper ();
            }
            return $ret;
        }
        return $ret;
    }
    
    /**
     * Helper function for findPrevAdjentTextNode
     *
     * Looks at the last child node; if it's a text node, it returns it,
     * if the element node did not have an open tag, it calls itself
     * recursively.
     */
    function &_findPrevAdjentTextNodeHelper () {
        $lastnode =& $this->lastChild ();
        if ($lastnode === null || $lastnode->_type == STRINGPARSER_NODE_TEXT) {
            return $lastnode;
        }
        if (!$lastnode->hadCloseTag ()) {
            $ret =& $lastnode->_findPrevAdjentTextNodeHelper ();
        } else {
            $ret = null;
        }
        return $ret;
    }
    
    /**
     * Get Flag
     *
     * @access public
     * @param string $flag The requested flag
     * @param string $type The requested type of the return value
     * @param mixed $default The default return value
     * @return mixed
     */
    function getFlag ($flag, $type = 'mixed', $default = null) {
        if (!isset ($this->_flags[$flag])) {
            return $default;
        }
        $return = $this->_flags[$flag];
        if ($type != 'mixed') {
            settype ($return, $type);
        }
        return $return;
    }
    
    /**
     * Set a flag
     *
     * @access public
     * @param string $name The name of the flag
     * @param mixed $value The value of the flag
     */
    function setFlag ($name, $value) {
        $this->_flags[$name] = $value;
        return true;
    }
    
    /**
     * Validate code
     *
     * @access public
     * @param string $action The action which is to be called ('validate'
     *                       for first validation, 'validate_again' for
     *                       second validation (optional))
     * @return bool
     */
    function validate ($action = 'validate') {
        if ($action != 'validate' && $action != 'validate_again') {
            return false;
        }
        if ($this->_codeInfo['callback_type'] != 'simple_replace' && $this->_codeInfo['callback_type'] != 'simple_replace_single') {
            if (!is_callable ($this->_codeInfo['callback_func'])) {
                return false;
            }
            
            if (($this->_codeInfo['callback_type'] == 'usecontent' || $this->_codeInfo['callback_type'] == 'usecontent?' || $this->_codeInfo['callback_type'] == 'callback_replace?') && count ($this->_children) == 1 && $this->_children[0]->_type == STRINGPARSER_NODE_TEXT) {
                // we have to make sure the object gets passed on as a reference
                // if we do call_user_func(..., &$this) this will clash with PHP5
                $callArray = array ($action, $this->_attributes, $this->_children[0]->content, $this->_codeInfo['callback_params']);
                $callArray[] =& $this;
                $res = call_user_func_array ($this->_codeInfo['callback_func'], $callArray);
                if ($res) {
                    // ok, now, if we've got a usecontent type, set a flag that
                    // this may not be broken up by paragraph handling!
                    // but PLEASE do NOT change if already set to any other setting
                    // than BBCODE_PARAGRAPH_ALLOW_BREAKUP because we could
                    // override e.g. BBCODE_PARAGRAPH_BLOCK_ELEMENT!
                    $val = $this->getFlag ('paragraph_type', 'integer', BBCODE_PARAGRAPH_ALLOW_BREAKUP);
                    if ($val == BBCODE_PARAGRAPH_ALLOW_BREAKUP) {
                        $this->_flags['paragraph_type'] = BBCODE_PARAGRAPH_ALLOW_INSIDE;
                    }
                }
                return $res;
            }
            
            // we have to make sure the object gets passed on as a reference
            // if we do call_user_func(..., &$this) this will clash with PHP5
            $callArray = array ($action, $this->_attributes, null, $this->_codeInfo['callback_params']);
            $callArray[] =& $this;
            return call_user_func_array ($this->_codeInfo['callback_func'], $callArray);
        }
        return (bool)(!count ($this->_attributes));
    }
    
    /**
     * Get replacement for this code
     *
     * @access public
     * @param string $subcontent The content of all sub-nodes
     * @return string
     */
    function getReplacement ($subcontent) {
        if ($this->_codeInfo['callback_type'] == 'simple_replace' || $this->_codeInfo['callback_type'] == 'simple_replace_single') {
            if ($this->_codeInfo['callback_type'] == 'simple_replace_single') {
                if (strlen ($subcontent)) { // can't be!
                    return false;
                }
                return $this->_codeInfo['callback_params']['start_tag'];
            }
            return $this->_codeInfo['callback_params']['start_tag'].$subcontent.$this->_codeInfo['callback_params']['end_tag'];
        }
        // else usecontent, usecontent? or callback_replace or callback_replace_single
        // => call function (the function is callable, determined in validate()!)
        
        // we have to make sure the object gets passed on as a reference
        // if we do call_user_func(..., &$this) this will clash with PHP5
        $callArray = array ('output', $this->_attributes, $subcontent, $this->_codeInfo['callback_params']);
        $callArray[] =& $this;
        return call_user_func_array ($this->_codeInfo['callback_func'], $callArray);
    }
    
    /**
     * Dump this node to a string
     *
     * @access protected
     * @return string
     */
    function _dumpToString () {
        $str = "bbcode \"".substr (preg_replace ('/\s+/', ' ', $this->_name), 0, 40)."\"";
        if (count ($this->_attributes)) {
            $attribs = array_keys ($this->_attributes);
            sort ($attribs);
            $str .= ' (';
            $i = 0;
            foreach ($attribs as $attrib) {
                if ($i != 0) {
                    $str .= ', ';
                }
                $str .= $attrib.'="';
                $str .= substr (preg_replace ('/\s+/', ' ', $this->_attributes[$attrib]), 0, 10);
                $str .= '"';
                $i++;
            }
            $str .= ')';
        }
        return $str;
    }
}

