<?php
namespace Controllers;

/*
	This class is used for storing session beyond alive client browser.
	The complete session stored in $_SESSION can be stored in the DB
	with an expiration date set by constant COOKIE_LENGTH. This is ideal
	for storing login for user with the "remember me"-login option.
*/
class Sessions{
	public static function get(&$db_connection, $session_id = $_COOKIE['SESSION']){
		//Getting session from DB with session ID and not yet expired
		$session = $db_connection->queryAndFetch('SELECT `data` FROM `sessions` WHERE `session_id` = \''. addslashes($session_id). '\' AND `expiration` > NOW() LIMIT 1');
		if (!empty($session['data'])){
			$_SESSION = json_decode($session['data']);
			
			return true;
		}
		
		//If no session was found, return false
		return false;
	}
	
	//Store session to DB
	public static function set(&$db_connection, $session_id = session_id()){
		//Store session to DB
		$db_connection->query('REPLACE INTO `sessions` (`session_id`, `data`, `expiration`) VALUES(\''. addslashes($session_id). '\', \''. json_decode($_SESSION). '\', \''. date('Y-m-d H:i:s', (time() + COOKIE_LENGTH)). '\'');
		
		//Create cookie with session ID
		setcookie('SESSION', $session_id, SERVER_COOKIE_PATH, SERVER_COOKIE_DOMAIN, SERVER_COOKIE_SECURE, false);
		
		//Everything worked out fine, return true
		return true;
	}
	
	//Find any stored session and return true if found. Good for just checking if a session exists or not
	public static function find(&$db_connection, $session_id = session_id()){
		$session = $db_connection->queryAndFetch('SELECT COUNT(*) as `total` FROM `sessions` WHERE `session_id` = \''. addslashes($session_id). '\' AND `expiration` > NOW() LIMIT 1');
		if (!empty($session['total']==1)){
			return true;
		}
		
		//If no session was found, return false
		return false;
	}
}