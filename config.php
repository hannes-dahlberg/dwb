<?php
define('DEFAULT_LANGUAGE', 'en');
define('COOKIE_LENGTH', (60 * 60 * 24 * 30));

define('USER_ERROR', '');
define('GROUP_ERROR', '');
define('EXCHANGE_RATE_ERROR', '');

//Libraries
include_once('libs/class.SqlConnect.inc.php');
include_once('libs/class.PasswordSalt.inc.php');
include_once('libs/class.Validate.inc.php');

//Sql-connection
include_once('secret/db_connection.php');

//Configs
define('SERVER_ADDRESS', 'http://localhost/dwb/');
define('SERVER_ROOT', $_SERVER['DOCUMENT_ROOT']. '/dwb/');
define('SERVER_COOKIE_PATH', '/dwb/');
define('SERVER_COOKIE_DOMAIN', 'localhost');
define('SERVER_COOKIE_SECURE', false);


//Include
include_once('functions.php');