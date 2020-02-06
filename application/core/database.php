<?php
namespace Framework;

use PDO;
use PDOException;

// http://php.net/manual/en/class.pdo.php
class Database {

	private $pdo = null;

	public static $host = 'localhost', $user = 'root', $pword = '', $dbname = '';

	function __construct($opt = array()) {
		$this->openConnection($opt);
	}

	public function openConnection($opt = array()) {
		try {
			if (!empty($opt) || count($opt) == 4) {
				$this->pdo = new PDO('mysql:dbname='.$opt['db'].';host='.$opt['host'], $opt['user'], $opt['password'], array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}else{
				$this->pdo = new PDO('mysql:dbname='.self::db.';host='.self::host, self::user, self::pword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			}
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		} catch (PDOException $e) {
			die ($e->getMessage());
		}
	}

	public function close() {
		return $this->pdo = null;
	}

	public function prepare($sql, $val) {
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($val);
		return $stmt;
	}

	// Functions as AUTO_INCREMENT. 
	public function getCount($table) {
		$stmt = $this->pdo->prepare('SELECT COUNT(*) AS `count` FROM ?');
		$stmt->execute(array($table));
		$count = $stmt->fetch()['count'];
		
		$stmt = $this->pdo->prepare('SELECT `id` FROM ? WHERE id=?');
		$stmt->execute(array($table, $count));
		return ($stmt->numRow() > 0 ? $count++ : $count);
	}
}

?>
