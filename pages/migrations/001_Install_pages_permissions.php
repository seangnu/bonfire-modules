<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Install_pages_permissions extends Migration {
	
	public function up() 
	{
		$prefix = $this->db->dbprefix;


		// permissions
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Content.View','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Content.Create','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Content.Edit','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Content.Delete','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Reports.View','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Reports.Create','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Reports.Edit','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Reports.Delete','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Settings.View','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Settings.Create','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Settings.Edit','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Settings.Delete','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Developer.View','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Developer.Create','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Developer.Edit','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
					$this->db->query("INSERT INTO {$prefix}permissions VALUES (0,'Pages.Developer.Delete','','active');");
					$this->db->query("INSERT INTO {$prefix}role_permissions VALUES (1,".$this->db->insert_id().");");
	}
	
	//--------------------------------------------------------------------
	
	public function down() 
	{
		$prefix = $this->db->dbprefix;

        // permissions
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Content.View';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Content.View';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Content.Create';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Content.Create';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Content.Edit';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Content.Edit';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Content.Delete';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Content.Delete';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Reports.View';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Reports.View';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Reports.Create';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Reports.Create';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Reports.Edit';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Reports.Edit';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Reports.Delete';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Reports.Delete';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Settings.View';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Settings.View';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Settings.Create';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Settings.Create';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Settings.Edit';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Settings.Edit';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Settings.Delete';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Settings.Delete';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Developer.View';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Developer.View';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Developer.Create';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Developer.Create';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Developer.Edit';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Developer.Edit';");
					$query = $this->db->query("SELECT permission_id FROM {$prefix}permissions WHERE name='Pages.Developer.Delete';");
					foreach ($query->result_array() as $row)
					{
						$permission_id = $row['permission_id'];
						$this->db->query("DELETE FROM {$prefix}role_permissions WHERE permission_id='$permission_id';");
					}
					$this->db->query("DELETE FROM {$prefix}permissions WHERE name='Pages.Developer.Delete';");
	}
	
	//--------------------------------------------------------------------
	
}