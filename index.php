<?php
namespace Framework;

if ( !defined('ABSPATH')) 
	define('ABSPATH', dirname(__FILE__).'/');

define('MODEL_PATH', ABSPATH . 'application/models/');
define('CONTROLLER_PATH', ABSPATH . 'application/controllers/');
define('VIEW_PATH', ABSPATH . 'application/models/views/');
define('LIBRARY_PATH', ABSPATH . 'application/libraries/');
define('CORE_PATH', ABSPATH . 'application/models/core/');
define('SCRIPT_PATH', ABSPATH . 'application/CronJobs/');
define('UPLOAD_PATH', ABSPATH . 'application/uploads/');

include CORE_PATH . 'util.php';
include CORE_PATH . 'database.php';

use Framework\Database;

define('TIMEZONE', 'Asia/Jerusalem');
define('WEBSITE_DOMAIN', 'http://localhost/');
define('WEBSITE_NAME', 'ilCapo01 Framework');
define('WEBSITE_VERSION', 3.0);
define('WEBSITE_AUTHOR', 'ilCapo01');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'YOUR_DB_NAME_COME_HERE');

ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
ini_set("log_errors", 1);
ini_set("error_log", '/php-error.log');
error_reporting(-1);
date_default_timezone_set(TIMEZONE);

// Allow to run scripts in cli environment, or as cron job.
if (!empty($argv[1])) { // php index.php [FILENAME]
	include CORE_PATH . 'cronjob.php';

	$opt = array('db' => Database::$dbname, 'host' => Database::$host, 'user' => Database::$user, 'password' => Database::$pword);
	$db = new Database($opt);
	$class = Util::ExecuteCronJob($argv[1], $db); // $_SERVER['argv']
	$class->init();
	$db = null;
	die;
}

define('USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
define('USER_IP', (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER['REMOTE_ADDR']));
define('USER_REFERER', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'));

if(!isset($_SESSION)) 
	session_start();

Util::detectMobile(USER_AGENT);

// Disable browser from cache files.
Util::disableCache();
// Anti-XSS\-SQLI
Util::processRequests();
Util::force_www(false);
//Util::force_ssl();

// Load framework's environment.
include CORE_PATH.'controller.php';
include CORE_PATH.'bootstrap.php';
include CORE_PATH .'model.php';

//https://support.google.com/webmasters/answer/93710
//https://developers.google.com/search/reference/robots_meta_tag
if (preg_match('/^(AOL)|(Baiduspider)|(bingbot)|(DuckDuckBot)|(Googlebot)|(Yahoo)|(YandexBot)$/', USER_AGENT)) {
	header('HTTP/1.1 200 OK');
	header('X-Robots-Tag: index, follow'); // noindex, nofollow, noarchive
}

// Probably a primitive bot is trying to access the website.
if (empty(USER_AGENT)) {
	header('HTTP/1.0 403 Forbidden');
	die;
} else { // A normal visitor trys to access the website.
	$controller = (isset($_GET['c']) ? $_GET['c'] : '');
	$boot = new Bootstrap($controller);
	$boot->init();
}

?>
