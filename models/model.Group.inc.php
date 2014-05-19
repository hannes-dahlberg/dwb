<?php
namespace Models;

class Group{
	private $_db_connection;
	
	public $id;
	public $name;
	public $title;
	public $description;
	
	public function __construct(&$db_connection, $params, $no_fetch = false){
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
			$this->_db_connection->query('INSERT INTO `groups` '. $sql_fields. ' '. $sql_values);
			$this->id = $this->_db_connection->insertId();
			
			//Get user
			$this->_fetch();
		}
	}
	
	public function update($params){
		$sql_update = '';
		foreach($params as $key => $value){
			$sql_update .= '`'. $key. '` = \''. $value. '\', ';
		}
		$sql_update = substr(0, -2);
		
		$this->_db_connection->query('UPDATE `groups` SET '. $sql_update. ' WHERE `id` = \''. $this->id. '\'');
		
		$this->_fetch();
	}
	
	private function _fetch(){
		if (!isset($this->id) || (isset($this->id) && empty($this->id)))
			die_with_error(GROUP_ERROR);
		
		$group = $this->_db_connection->queryAndFetch('SELECT * FROM `groups` WHERE `id` = \''. $this->id. '\' LIMIT 1');
		
		if (empty($group['id']))
			die_with_error(GROUP_ERROR);
		
		$this->name = $group['name'];
		$this->title = \Controllers\Translations::get($this->_db_connection, $group['title']);
		$this->description = \Controllers\Translations::get($this->_db_connection, $group['description']);
	}
}