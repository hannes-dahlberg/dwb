<?php
class Validate
{
	public static function email($email){
		
	}
	public static function password($password){
		if (preg_match('/^.{8,255}$/', $password))
			return true;
		
		return false;
	}
}
?>