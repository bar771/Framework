<?php
namespace Framework\CronJobs;

use Framework\CronJob as baseObject;

use Framework\Database;

class verifyPremium extends baseObject {
	function __construct($database) {
		parent::__construct($database);
	}

	public function init() {
		// Loop through all users who bought premium.
		$sess = $database->prepare('SELECT * FROM users WHERE isPremium=:premium');
    $sess->bindValue(':premium', 1)
		$sess->closeCursor();
		$sess->execute();
		$rows = $sess->fetchAll();

		for ($i=0; $i<sizeof($rows); $i++) {
			$row = $rows[$i];

			$diff = floor((intval($row['premiumExpiry']) - time()) / 60 / 60 / 24);
			if ($diff <= 0) {
				$sess = $database->prepare('UPDATE FROM users SET isPremium=:premium, premiumExpiry=:expired WHERE id=:id');
				$sess->bindValue(':id', $row['id']);
        $sess->bindValue(':premium', 0);
        $sess->bindValue(':expired', 0);
				$sess->closeCursor();
				$sess->execute();
			}
		}

		$database->close();
	}

}
?>
