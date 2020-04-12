<?php
namespace Framework;

if ( !defined('ABSPATH'))
	define('ABSPATH', dirname(__FILE__).'/');

define('MODEL_PATH', ABSPATH . 'application/models/');
define('CONTROLLER_PATH', ABSPATH . 'application/controllers/');
define('VIEW_PATH', ABSPATH . 'application/views/');
define('LIBRARY_PATH', ABSPATH . 'application/libraries/');
define('CORE_PATH', ABSPATH . 'application/core/');
define('SCRIPT_PATH', ABSPATH . 'application/CronJobs/');
define('UPLOAD_PATH', ABSPATH . 'application/uploads/');
define('MEDIA_PATH', ABSPATH . 'application/uploads/media/');

define('DEVELOPMENT_MODE', 1);
define('PARAM_CLI', $_SERVER['argv']); // $argv
define('RAW_DATA', file_get_contents("php://input"));

ob_start();
include CORE_PATH . 'config.php';
include CORE_PATH . 'util.php';
include CORE_PATH . 'database.php';
if (count(PARAM_CLI) > 0) include CORE_PATH . 'cronjob.php';
ob_end_flush();

use Framework\Database;
use Exception;

ini_set('error_reporting', -1);
ini_set('display_startup_errors', DEVELOPMENT_MODE);
ini_set('display_errors', DEVELOPMENT_MODE);
ini_set("log_errors", 1);
ini_set("error_log", ABSPATH . 'application/php-error.log');
date_default_timezone_set(TIMEZONE);

$db = null;

try {
	$db = new Database(array('db' => DB_NAME, 'host' => DB_HOST, 'user' => DB_USER, 'password' => DB_PASS));
} catch (Exception $e) {
	error_log($e); throw new Exception($e);
}

// Allow to run scripts in cli environment, or as cron job.
if (!empty($argv[1])) { // php index.php [FILENAME]
	$class = Util::ExecuteCronJob($argv[1], $db); // $_SERVER['argv']
	$class->init();
	$db->close();
	die;
}

define('USER_AGENT', $_SERVER['HTTP_USER_AGENT']);
define('USER_IP', (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER['REMOTE_ADDR']));
define('USER_REFERER', (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'));

if(!isset($_SESSION))
	session_start();

//https://support.google.com/webmasters/answer/93710
//https://developers.google.com/search/reference/robots_meta_tag
$bot_headers = array('index, follow' , 'noindex, nofollow, noarchive');
if (preg_match('/^(AOL)|(Baiduspider)|(bingbot)|(DuckDuckBot)|(Googlebot)|(Yahoo)|(YandexBot)$/', USER_AGENT)) {
	header('HTTP/1.1 200 OK');
	header('X-Robots-Tag: index, follow'); // noindex, nofollow, noarchive
}

Util::detectMobile(USER_AGENT);

//Util::disableCache();
Util::force_www(false);
//Util::force_ssl();

// Load framework's environment.
include CORE_PATH . 'controller.php';
include CORE_PATH . 'bootstrap.php';
include CORE_PATH .'model.php';

$boot = null;

if (empty(USER_AGENT)) {
	header('HTTP/1.0 403 Forbidden');
	die;
} else {
	try {
		$server = $_SERVER;

		$host = $server['HOST_NAME'];
		$path = $server['REQUEST_URI']; // QUERY_STRING

		//$controller = (isset($_GET['c']) ? $_GET['c'] : '');

		// Filter input from user.
		foreach ($_POST as $var => $val) {
			htmlspecialchars(rtrim($val), ENT_QUOTES);
			$_POST[$var] = $val;
		}

		foreach ($_GET as $var => $val) {
			htmlspecialchars(rtrim($val), ENT_QUOTES);
			$_GET[$var] = $val;
		}

		$boot = new Bootstrap($path, $db);
		$boot->init();
	} catch (Exception $e) {
		error_log($e); throw new Exception($e);
	}
}
$db->close();

?>
