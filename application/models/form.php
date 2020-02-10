<?php
namespace Framework\Models;

use Framework\Model as baseObject;

class Form extends baseObject {

  private $fields = array();

	function __construct($database = null) {
		parent::__construct($database);
	}

	function init() {
		parent::init();
	}

  function startForm($method='POST', $action = '/', $type = '') {
    $tag = '<form action="'.$action.'" method="'.method.'" type="'.$type.'">';
    array_push($this->$fields, $tag);
  }

  function endForm() {
    array_push($this->$fields, '</form>');
  }

  function addField($attr = array()) {
    $tag = '<input ';
    foreach($attributes as $attr=>$value) {
      $tag .= $attr.'="'.$value.'" ';
    }
    array_push($this->$fields, $tag.'/>');
  }

  function __toString() {
    $str = '';
    foreach($this->fields as $line)
      $str .= $line.'<br>';
    return $str;
  }

}

?>
