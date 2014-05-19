<?php
namespace Controllers;

class Translations{
	public static function get(&$db_connection, $translation_id){
		//Set language to default language
		$language = DEFAULT_LANGUAGE;
		
		//If language is set from session, set language from session
		if (!isset($_SESSION['language'])){
			$language = $_SESSION['language'];
		}
		
		//Fetch translation value from DB, if it can't be found in current set language, the default language translation will be fetch
		$translation = $db_connection->queryAndFetch('
			SELECT
				`translation_values`.`value` AS `value`
				IF (`languages`.`code` = \''. $language. '\', 1, 0) AS `first_choice`
			FROM
				`translations`
			INNER JOIN `translation_values` ON
				`translations`.`id` = `translation_values`.`translation`
			INNER JOIN `languages` ON
				`translation_values`.`language` = `languages`.`id`
			WHERE
				`translations`.`translation_id` = \''. addslashes($translation_id). '\' AND
				(
					`languages`.`code` = \''. $language. '\' OR
					`languages`.`code` = \''. DEFAULT_LANGUAGE. '\'
				)
			ORDER BY
				`first_choice` DESC
			LIMIT
				1
		');
		
		if (!empty($translation['value'])){ //Return translation
			return $translation['value'];
		}else{ //If no translation was found return the translation ID varchar
			return '_'. $translation_id;
		}
	}
	
	public static function set(&$db_connection, $value, $translation_id, $language = false){
		//Check if language to set is set
		if (!$language){ //If not set using default or set from session
			$language = DEFAULT_LANGUAGE;
			if (!isset($_SESSION['language'])){
				$language = $_SESSION['language'];
			}
		}
		
		//Add translation id to DB if not existing
		$db_connection->query('REPLACE INTO `translations` (`translation_id`) VALUE(\''. addslashes($translation_id). '\')');
		
		//Get translation and language from DB for use when inserting translation value
		$translation = $db_connection->queryAndFetch('SELECT `id` FROM `translations` WHERE `translation_id` = \'' . addslashes($translation_id). '\' LIMIT 1');
		$language = $db_connection->queryFetchAll('SELECT `id` FROM `languages` WHERE `code` = \''. addslashes($language). '\' LIMIT 1');
		
		//Insert translation value (or replace)
		$db_connection->query('REPLACE INTO `translation_values` (`translation`, `language`, `value`) VALUES(\''. $translation['id']. '\', \''. $language['id']. '\', \''. addslashes($value). '\')');
		
		//Return translation
		return self::get($db_connection, $translation_id);
	}
}