<?php
namespace Models;

class User{
	private $_db_connection;
	
	public $id;
	public $username;
	public $email;
	public $registered;
	public $last_seen;
	
	private $_hash;
	private $_salt;
	
	public $groups = array();
	
	/**
	* Creating a new user object
	* 
	* @param SqlConnect $db_connection a connection to the database
	* @param array $params parameters for user
	* @return void
	*
	* $params['id'] User ID (if empty will insert, otherwise fetch from DB)
	* $params['username'] Username to insert
	* $params['email'] Email to insert
	* $params['hash'] Hash password to insert
	* $params['salt'] Hash salt for password to insert
	*/
	public __construct(&$db_connection, $params, $no_fetch = false){
		$this->_db_connection = &$db_connection;
		
		//No fetch is for just creating the object without any database involvement
		if ($no_fetch){
			foreach($params as $key => $value){
				if (isset($this->$key)){
					$this->$key = $value;
				}
			}
			
			return true;
		}
		
		if (isset($params['id'])){ //If user user ID is set, trying to fetch user
			//Set user ID
			$this->id = $params['id'];
			//Get user
			$this->_fetch();
		}else{ //If no ID was set, a new user is inserted
			$sql_fields = '';
			$sql_values = '';
			foreach($params as $key => $value){
				$sql_fields .= '`'. $key. '`, ';
				$sql_values .= '\''. $value. '\'';
			}
			$sql_fields = '('. substr($sql_fields, 0, -2). ')';
			$sql_values = 'VALUES('. substr($sql_values, 0, -2). ')';
			
			//Insert user
			$this->_db_connection->query('INSERT INTO `users` '. $sql_fields. ' '. $sql_values);
			$this->id = $this->_db_connection->insertId();
			
			//Get user
			$this->_fetch();
		}
	}
	
	/**
	* Update user
	* 
	* @param array $params parameters for user
	* @return void
	*
	* $params['username'] Username to update
	* $params['email'] Email to update
	* $params['hash'] Hash password to update
	* $params['salt'] Hash salt for password to update
	* $params['group_id'] Group ID for update
	* $params['group_name'] Group name (will first try to use ID)
	*/
	public function update($params){
		$sql_update = '';
		foreach($params as $key => $value){
			$sql_update .= '`'. $key. '` = \''. $value. '\', ';
		}
		$sql_update = substr(0, -2);
		
		$this->_db_connection->query('UPDATE `users` SET '. $sql_update. ' WHERE `id` = \''. $this->id. '\'');
		
		$this->_fetch();
	}
	
	public function add_group($group){
		$this->_db_connection->query('REPLACE INTO `users_to_groups` (`user`, `group`) VALUES(\''. $this->id. '\', \''. $group. '\')');
		
		$this->_fetch();
	}
	
	private function _fetch(){
		if (!isset($this->id) || (isset($this->id) && empty($this->id)))
			die_with_error(USER_ERROR);
		
		$user = $this->_db_connection->queryAndFetch('SELECT * FROM `users` WHERE `id` = \''. $this->id. '\' LIMIT 1');
		
		if (empty($user['id']))
			die_with_error(USER_ERROR);
		
		$this->username = $user['username'];
		$this->email = $user['email'];
		$this->_hash = $user['hash'];
		$this->_salt = $user['salt'];
		$this->registered = $user['registered'];
		$this->last_seen = $user['last_seen'];
		
		//Fetching any groups for user
		$groups = $this->_db_connection->queryAndFetchAll('SELECT `group` FROM `users_to_groups` WHERE `user` = \''. $this->id. '\'');
		//Store groups in user group array
		foreach($groups as $group){
			$this->groups[] = new Group($this->_db_connection, array('id' => $group['group']));
		}
	}
}