<?php
namespace Models;

class ExchangeRate{
	private $_db_connection;
	
	public $id;
	public $currency;
	public $date;
	public $value;
	
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
		
		if (isset($params['id'])){ //If exhcnage_rate ID is set, trying to fetch exchange rate
			//Set exchange rate ID
			$this->id = $params['id'];
			//Get exchange_rate
			$this->_fetch();
		}else{ //If no ID was set, a new exchange is inserted
			$sql_fields = '';
			$sql_values = '';
			foreach($params as $key => $value){
				$sql_fields .= '`'. $key. '`, ';
				$sql_values .= '\''. $value. '\'';
			}
			$sql_fields = '('. substr($sql_fields, 0, -2). ')';
			$sql_values = 'VALUES('. substr($sql_values, 0, -2). ')';
			
			//Insert exchange rate
			$this->_db_connection->query('INSERT INTO `exchange_rates` '. $sql_fields. ' '. $sql_values);
			$this->id = $this->_db_connection->insertId();
			
			//Get exchange rate
			$this->_fetch();
		}
	}
	
	public function update($params){
		$sql_update = '';
		foreach($params as $key => $value){
			$sql_update .= '`'. $key. '` = \''. $value. '\', ';
		}
		$sql_update = substr(0, -2);
		
		$this->_db_connection->query('UPDATE `exchange_rates` SET '. $sql_update. ' WHERE `id` = \''. $this->id. '\'');
		
		$this->_fetch();
	}
	
	private function _fetch(){
		if (!isset($this->id) || (isset($this->id) && empty($this->id)))
			die_with_error(GROUP_ERROR);
		
		$exchange_rate = $this->_db_connection->queryAndFetch('SELECT * FROM `exchange_rates` WHERE `id` = \''. $this->id. '\' LIMIT 1');
		
		if (empty($exchange_rate['id']))
			die_with_error(EXCHANGE_RATE_ERROR);
		
		$this->currency = new Group($this->_db_connection, array('id' => $exchange_rate['currency']));;
		$this->date = $exchange_rate['date'];
		$this->value = $exchange_rate['value'];
	}
}