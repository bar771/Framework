<?php
namespace Framework;

use Framework\Controller as objUtil; 

class Model {
	protected $database = null;
	
	function __construct($db = null) {
		$this->database = $db;
	}
	
	function init() {
		
	}
}

?>