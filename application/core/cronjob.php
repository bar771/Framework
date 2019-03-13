<?php
namespace Framework;

use Framework\Database;

class CronJob {
  protected $database = null;

  /*
   * @param object $database
   * Constructor method.
  **/
  function __construct($database = null) {
    $this->database = $database;
  }
}

?>
