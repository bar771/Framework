<?php
namespace Framework\CronJobs;

use Framework\CronJob as baseObject;

use Framework\Database;

class verifySession extends baseObject {
	function __construct($database) {
		parent::__construct($database);
	}

	public function init() {
		// Loop through all users' sessions.
		$sess = $database->prepare('SELECT * FROM user_sessions');
		$sess->closeCursor();
		$sess->execute();
		$rows = $sess->fetchAll();

		$timeToLogout = 15; // In minutes.
		for ($i=0; $i<sizeof($rows); $i++) {
			$row = $rows[$i];

			$diff = floor((time() - (int)$row['time']) / 60);
			if ($diff >= $timeToLogout) {
				$sess = $database->prepare('DELETE FROM user_sessions WHERE id=:id');
				$sess->bindValue(':id', $row['id']);
				$sess->closeCursor();
				$sess->execute();
			}
		}

		$database->close();
	}

}
?>
