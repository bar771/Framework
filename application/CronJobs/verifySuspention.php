<?php
namespace Framework\CronJobs;

use Framework\CronJob as baseObject;

use Framework\Database;

class verifySession extends baseObject {
	function __construct($database) {
		parent::__construct($database);
	}

	public function init() {
		// Loop through all users.
		$stmt = $database->prepare('SELECT * FROM users WHERE isBlocked=:block');
		$stmt->bindValue(':block', 1);
		$stmt->closeCursor();
		$stmt->execute();
		$rows = $stmt->fetchAll();

		for ($i=0; $i<sizeof($rows); $i++) {
			$row = $rows[$i];

			if ($row['blockExpiry'] == 0) continue;

			$diff = floor((int)$row['blockExpiry'] - time());
			if ($diff <= 0) {
				$stmt = $database->prepare('UPDATE users SET isBlocked=:block, blockExpiry=:exp WHERE id=:id');
				$stmt->bindValue(':block', 0);
				$stmt->bindValue(':exp', null);
				$stmt->bindValue(':id', $row['id']);
				$stmt->closeCursor();
				$stmt->execute();
			}
		}

		$database->close();
	}
}
?>
