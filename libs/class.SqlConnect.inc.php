<?php
/**
* Class made for easier keep hold of mysql connections and be able
* to query a specific db connection, get all the rows in one
* request and such.
*
* This class is purely made for in-housing Tiego work and is not
* to be distribute
* 
* @author Hannes Dahlberg <hannes.dahlberg@tiego.se>
* @copyright 2014 Tiego AB
*/
class SqlConnect{
	/**
	* The connection property
	*
	* @var mysqli for holding the connection to the database
	*/
	private $_connection;
	
	/**
	* Admin property to show error messages or not
	* 
	* @var bool if the admin privileges should exists or not 
	*/
	private $_admin = false;
	
	/**
	* This variable is for holding the current working database
	* 
	* @var string the name of the database
	*/
	private $_database = false;
	
	/**
	* creates the connection to the database upon initiation
	* 
	* @param string $address the address to the database
	* @param string $username the username login for the database
	* @param string $password the password login for the database
	* @param string $database the specific database for the connection
	* @return void 
	*/
	public function __construct($address, $username, $password, $database){
		$this->_connection = new mysqli($address, $username, $password);
		$this->_connection->set_charset('utf8');
		
		//Set the databasename
		$this->database($database);
	}
	
	/**
	* Set the admin privileges
	* 
	* @param bool $admin the value of admin property
	*/
	public function setAdmin($admin){
		if ($admin===true){
			$this->_admin = true;
		}else{
			$this->_admin = false;
		}
	}
	
	public function setCharset($charset){
		$this->_connection->set_charset($charset);
	}
	
	/**
	* Get or set database for the db-connection
	* 
	* @param string $database the database to change to
	* @return void/string the name of the database if $database is false, else void
	*/
	public function database($database = false){
		//Set the database
		if ($database){
			$this->_connection->select_db($database);
			//Store the new settings in our property
			$this->_database = $database;
		}else{
			return $this->_database;
		}
	}
	
	/**
	* Query the database with an sql-query
	* 
	* @param string $sql the query
	* @param bool $multi if it is multiple queries or not
	* @return mysqli_result/void depending on success the result of the query will return or exit on failure
	*/
	public function query($sql, $multi = false)
	{
		$functionName = 'query';
		if ($multi){
			$functionName = 'multi_query';
		}
		
		//tries to execute the query ether as a multi query or not depending on $functionName
		if ($sqlResult = $this->_connection->$functionName($sql)){
			return $sqlResult;
		}else{
			$error = true;
		}
		
		//If execution failed and $_admin is true, an error is displayed
		if ($this->_admin && isset($error))
			echo 'MySQL error: '. $this->_connection->error. '<br />MySQL query: <pre>'. $sql. '</pre>';
		
		//If execution failed the script is stoped
		if (isset($error))
			exit();
	}
	
	/**
	* Function for fetch the first row of an sql statment result
	* 
	* @param string $sql the query to be executed
	* @return array from this class fetchAssic
	*/
	public function queryAndFetch($sql){
		return $this->fetchAssoc($this->query($sql));
	}
	/**
	* Function for fetching all the rows of an sql statment result
	* 
	* @param string $sql the sql query
	* @return array containing all rows of sql result
	*/
	public function queryAndFetchAll($sql){
		//creates array to hold rows
		$resultArray = array();
		
		//Get the query result and loop throw every row
		$sql = $this->query($sql);
		while($sqlResult = $this->fetchAssoc($sql)){
			//save every row in the result array
			$resultArray[] = $sqlResult;
		}
		
		//Return the result array
		return $resultArray;
	}
	
	/**
	* Function for fetching a row from an sql result
	* 
	* @param mysqli_result $sqlResult the result of an sql-statement
	* @return array for current row of result
	*/
	public function fetchAssoc($sqlResult){
		return $sqlResult->fetch_assoc();
	}
	
	/**
	* Function to get the latest insterted ID
	* 
	* @return latest ID from auto increment
	*/
	public function insertId(){
		return $this->_connection->insert_id;
	}
}
?>