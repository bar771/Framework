<?php
namespace Framework;

include Util::getFile('core/database.php');

use Framework\Database;

if ( !defined('ABSPATH')) 
	define('ABSPATH', dirname(__FILE__).'/');

define('MODEL_PATH', ABSPATH . 'application/models/');
define('CONTROLLER_PATH', ABSPATH . 'application/controllers/');
define('VIEW_PATH', ABSPATH . 'application/models/views/');
define('LIBRARY_PATH', ABSPATH . 'application/models/libraries/');
define('CORE_PATH', ABSPATH . 'application/models/core/');
define('SCRIPT_PATH', ABSPATH . 'application/CronJobs/');

define('TIMEZONE', 'Asia/Jerusalem');
define('WEBSITE_DOMAIN', 'http://localhost/');
define('WEBSITE_NAME', 'ilCapo01 Framework');
define('WEBSITE_VERSION', 3.0);
define('WEBSITE_AUTHOR', 'ilCapo01');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', '[YOUR_DB_NAME_COME_HERE]');

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
define('REVERSE_PROXY_DOMAIN', $_SERVER['HTTP_DOMAIN']); // Reverse Proxy

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

// Probably a primitive bot is trying to access the website.
if (empty(USER_AGENT)) {
	header('HTTP/1.0 403 Forbidden');
	die;
} else { // A normal visitor trys to access the website.
	$controller = (isset($_GET['c']) ? $_GET['c'] : '');
	$boot = new Bootstrap($controller);
	$boot->init();
}

/**
 * Utility class.
 **/
class Util {
// Convert htaccess to nginx
// https://www.winginx.com/en/htaccess

	/*
	 * @param string $filename
	 * @param object $db
	 * @return object
	 * Execute php script in cli environment.
	**/
	static function ExecuteCronJob($filename = '', $db) {
		if (!empty($path = SCRIPT_PATH . $filename . '.php' )) {
			include $path;
			$class = 'Framework\\CronJobs\\'.$filename;
			return new $class($db);
		}
	}

	/*
	 * @param string $model
	 * @return void
	 * Load models.
	**/
	static function loadModel($model = '') {
		$model = (!empty($model) ? MODEL_PATH . $model . '.php' : '');
		include $model;
	}

	/*
	 * @param string $library
	 * @return void
	 * Load libraries.
	**/
	static function loadLibrary($library = '') {
		$library = (!empty($library) ? LIBRARY_PATH . $library . '.php' : '');
		include $library;
	}

	/*
	 * @param string $filename
	 * @param $redirectURL
	 * @return void
	 * Allow you to block access to certain php in the root directory.
	**/
	static function blockAccess($file, $redirectURL = '/') {
		if ($_SERVER['REQUEST_URI'] == $file) {
			header('HTTP/1.1 403 Forbidden');
			header('location: '.$redirectURL);
		}
	}

	/*
	 * @return void
	 * Send headers to disable caching for the website.
	**/
	static function disableCache() {
		header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}

	/*
	 * @param boolean $on
	**/
	static function force_www($on = true) {
		if ($on) {
			if (substr($_SERVER['HTTP_HOST'], 0, 3) != 'www') {
				header('HTTP/1.1 301 Moved Permanently');
				header('location: http://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
				exit;
			}
		}
	}

	static function force_ssl() {
		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off") {
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			exit;
		}
	}

	/*
	 * @param string $url
	 * @param array $json
	 * @return object / array
	**/
	static function sendJSON($url = '', $json = array()) {
		$content = json_encode($json);

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER,
		        array("Content-type: application/json"));
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
		$json_response = curl_exec($curl);
		$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ( $status != 201 )
		    die("Error: call to URL ".$url." failed with status ".$status.", response ".$json_response.", curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl));
		curl_close($curl);
		return json_decode($json_response, true);
	}

	/*
	 * @return array
	 * Receives data from the user in json format and converts it to an array.
	**/
	static function receiveJSON() {
		$v = json_decode(stripslashes(file_get_contents("php://input")));
		if(empty($v))  die('Not found');
		return $v;
	}

	/*
	 * @param string $c
	 * @return binary (image/png)
	**/
	static function textToQR($c) {
		//return self::sendJSON('https://tippin.me/qrcode.php?c='.$c);
		return self::sendJSON('https://chart.googleapis.com/chart?chs=50x50&cht=qr&chl='.urlencode($c).'&chld=L|1&choe=UTF-8');
	}


	/*
	 * @param string $useragent
	 * @return void
	**/
	static function detectMobile($useragent) {
		// http://detectmobilebrowsers.com/
		if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
			{
				echo '
				<!DOCTYPE html>
				<html>
					<head>
						<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
						<title>Mobile not supported</title>
					</head>
					<body>
						<!-- Display AppStore \ Google Play redirect. -->
						<img src="'.WEBSITE_DOMAIN.'assets/images/playstore.png">
						<img src="'.WEBSITE_DOMAIN.'assets/images/appstore.png">
					</body>
				</html>
				';
				die;
			}
	}

	static function _ago($time) {
		$diff = (time() - $time);
		if (($diff) < 60)
			return ($diff/60).' seconds(s) ago';
		else if (($diff/60) < 60)
			return ($diff/60).' minute(s) ago';
		else if (($diff/60/60) < 60)
			return ($diff/60).' hour(s) ago';
		else if (($diff/60/60/24) < 24)
			return ($diff/60).' day(s) ago';
		else if (($diff/60/60/24/7) < 7)
			return ($diff/60).' week(s) ago';
		else if (($diff/60/60/24/30) < 30)
			return ($diff/60).' month(s) ago';
		return ($diff/60/60/24/30/12).' year(s) ago';
	}
}
?>
