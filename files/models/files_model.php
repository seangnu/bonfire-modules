<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Files_model extends BF_Model {

	protected $table		= "files";
	protected $key			= "id";
	protected $soft_deletes         = FALSE;
        protected $date_format          = "datetime";
	protected $set_created          = TRUE;
	protected $set_modified         = FALSE;
        
    /**
     * Generates the LIKE portion of a query.
     *
     * @param string $field  Field Name
     * @param string $value  Value to look for
     * @param string $type   Where to place % symbols, supports [before|after|both] defaults to both
     *
     * @return BF_Model|$this
     */
    public function likes( $field = '', $value = '', $type = 'both')
    {
        $this->db->like ( $field, $value, $type );

        return $this;
    }
}
