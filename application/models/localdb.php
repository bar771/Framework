<?php
namespace Framework\Models;

use SQLite3;

use Framework\Model as baseObject;

//http://php.net/manual/en/class.sqlite3.php
class LocalDB extends baseObject {

	function __construct($filename) {
		parent::__construct($this->openConnection($filename));
	}

	function init() {
		parent::init();
	}

	public function openConnection($filename) {
		try {
			$db = new SQLite3($filename);
		} catch(Exception $e) {
			die($e->getMessage());
		}
		return (isset($db) ? $db : null);
	}

	// Should be use to install tables, dbs, alter, etc..
	public function executeCommand($str) {
		return $this->database->exec($str);
	}
}

?>
