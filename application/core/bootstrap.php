<?php
namespace Framework;

use Framework\Util;
use Framework\Database as objDB;

define('FRAMEWORK_PHP_VERSION', '^7.0');
define('CURRENT_PHP_VERSION', phpversion());

class Bootstrap {
	private $controller = '';

	function __construct($controller = '') {
		$this->isSupported();
		$this->controller = $controller;
	}

/*	public function init() {
		if (!empty($this->controller)) {
			$controller = explode('/', $this->controller);
			$file = Util::getFile('controllers/'.$controller[0].'.php');

			if (!empty($file)) {
				if (strtolower($controller[0]) == 'index' && sizeof($controller) > 1) {
					$controller[0] = '';
					header('location: '.implode('/', $controller));
				}else if (strtolower($controller[0]) == 'index'){
					header('location: /');
				}
				include $file;

				if (empty($controller[1])) {
					$class = 'Framework\\Controllers\\'.$controller[0];
					$page = new $class();
					$page->init();
				}else {
					if (preg_match('/^[1-9][0-9]{0,15}$/', $controller[1])) {
						$class = 'Framework\\Controllers\\'.$controller[0];
						$page = new $class($controller[1]);

						if (!empty($controller[2]))
							$page->{$controller[2]}();
					}else {
						$class = 'Framework\\Controllers\\'.$controller[0];
						$page = new $class();

						if (method_exists($page, $controller[1])) {
							if (!empty($controller[2])) {
								if (!empty($controller[3]))
									$page->{$controller[1]}($controller[2], $controller[3]);
								else
									$page->{$controller[1]}($controller[2]);
							}
							else $page->{$controller[1]}();
						}else {
							if (!empty($controller[1]))
								if (!empty($controller[2]))
									$page->init($controller[1], $controller[2]);
								else
									$page->init($controller[1]);
						}
					}
				}
			}else {
				include Util::getFile('controllers/index.php');
				$page = new Controllers\Index();

				if (method_exists($page, $controller[0])) {
					if (!empty($controller[1])) {
						if (!empty($controller[2]) ) // /method/param1/param2
							$page->{$controller[0]}($controller[1], $controller[2]);
						else // /method/parameter
							$page->{$controller[0]}($controller[1]);
					}else {
						$page->{$controller[0]}();
					}
				}else {

//https://stackoverflow.com/questions/2015985/using-header-to-rewrite-filename-in-url-for-dynamic-pdf#2016016
					if (sizeof($controller) > 0) {
						$path = implode('/', $controller);
						$path = Util::getFile('uploads/'.$path);

						//if (file_exists($path) && !is_dir($path)) {
						if (!empty($path)) {
							$this->getResources($path);
						}else {
							echo '<!DOCTYPE html><html dir="rtl"><head><title>הדף לא נמצא !</title><style type="text/css">.text-center{text-align:center;}.text{font-size:35px;font-weight:bold;}a{color:#000;text-decoration:none;border-bottom:1px dotted #000;}</style></head><body><div class="text text-center">הדף לא נמצא !</div><div class="text-center"><a href="javascript:void(0);" onclick="javascript:window.location.href = \'/\';">לחזרה לעמוד הראשי לחץ כאן !</a></div></body></html>';
							header('Content-Type: text/html');
							header("HTTP/1.0 404 Not Found");
						}
					}
				}
			}
		}else {
			include Util::getFile('controllers/index.php');
			$page = new Controllers\Index();
			$page->init();
		}
	}*/


	function init() {
		$boot = $this->route($this->controller);

		$class = 'Framework\\Controllers\\'.$boot[1][0];
		$page = new $class;
		$page->init();

	}


	private function route($path) {
	  $entrypoints = array();

	  $entrypoints['/%c/'] = 'load_controller';
	  $entrypoints['/%c/%m'] = 'load_controller';
	  $entrypoints['/%c/%m/%p'] = 'load_controller';

	  $reached = false;

	  list($request) = explode('?', $path);

	  foreach ($entrypoints as $id => $fun) {
	    $id = '@^' . preg_quote($id, '@') . '$@u';

	    $id = str_replace('%c', '([a-zA-Z]{1,58})', $id);
	    $id = str_replace('%m', '([a-zA-Z]+)', $id);
	    $id = str_replace('%p', '([a-zA-Z0-9]+)', $id);

	    $matches = null;

	    if (preg_match($id, $request, $matches)) {

	      array_shift($matches);

	      $reached = array($fun, $matches);

	      break;
	    }
	  }

	  return $reached;
	}

	private function getResources($path, $downloadPopup = false, $cache = true) {
		$fileSize = filesize($path);
		$chunkSize = 1;
		$delay = 0;//0.001
		$sizeLimit = 1048576*5;

		// Not allowing bots access files, and website owners to access files from their site.
		if (USER_AGENT == '' || USER_AGENT == null) {
			die;
		}

		// Check if the file's empty.
		if ($fileSize <= 0) {
			header('Content-Type: html/plain');
			echo '<!DOCTYPE html><html><head><title>Empty File...</title><style type="style/css">.title { font-size: 20px;}</style></head><body><span class="title">Empty file...</span><p>WTF...</p></body</html>';
			return;
		}

		// Determine Content-Type.
		switch(pathinfo($path, PATHINFO_EXTENSION)) {
			case 'php': // SECURITY BREACH !
				header('Content-Type: text/plain');
				echo 'YOU SNEAKY MOTHER TRACKER `_` ';
				return;
			case 'png':
				$cache = true;
				header('Content-Type: image/png');
				break;
			case 'gif':
				header('Content-Type: image/gif');
				break;
			case 'jpg':
			case 'jpeg':
				header('Content-Type: image/jpg');
				break;
			case 'js':
				header('Content-Type: text/javascript');
				break;
			case 'css':
				header('Content-Type: text/css');
				break;
			case 'swf':
				if (!objDB::isUserLoggedIn()) {
					die('Invalid.');
				}
				header('Content-Type: application/x-shockwave-flash');
				break;
			case 'exe':
			case 'zip':
			case 'rar':
			case '7zip':
			case 'tar.gz':
			//case 'tbz2':
			case 'gz':
				if (!objDB::isUserLoggedIn() || !objDB::isUserHasPrivilege($_POST['privilege'])) {
					echo 'Invalid.';
					return;
				}
				header('Content-Type: application/octet-stream');
				break;
			case 'txt':
			default:
				header('Content-Type: text/plain');
				break;
		}

		// Tells the browser to pop-up the download manager.
		if ($downloadPopup) {
			header('Content-Length: '.filesize($path));
			$p = explode('/', $path);
			header('Content-Disposition: attachment; filename='.$p[sizeof($p)]);
		}

		// Change transfer speed and rate.
		if ($fileSize >= $sizeLimit) {
			$chunkSize = 5;
			$delay = .001;
		}

		// TODO: Enable cache for this file.
		if ($cache) {
			header("Cache-Control: private, max-age=10800, pre-check=10800");
			header("Pragma: private");
			header("Expires: " . date(DATE_RFC822,strtotime("+2 day")));
		}

		// Download file from the host.
		set_time_limit(0);
		$fp = fopen($path, 'r');
		/*while (($buff = fgets($fp)) != false) {
			print fread($fp, $chunkSize);
			sleep($delay);
		}
		if (!feof($fp)) echo 'Couldnt read the whole file.';*/
		while (!feof($fp)) {
			print fread($fp, $fileSize/$chunkSize);
			sleep($delay);
		}
		fclose($fp);
	}

	private function isSupported() {
		if (version_compare(CURRENT_PHP_VERSION, FRAMEWORK_PHP_VERSION, '<'))
			die ('The framework supports PHP '.FRAMEWORK_PHP_VERSION);
	}
}


?>
