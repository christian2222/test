<?php

require('EditObject.php');

class EditFactory {

	protected $editObject;

	public function __construct() {
		$this->editObject = new EditObject();
		$this->clear();
	}

	public function clear() {
		$this->editObject->loadEntries('','','');
		$this->editObject->loadIds('','','');
		$this->editObject->loadJavascript('','');
	}

	public function loadCdEntry($number,$cdEntry) {
		$this->editObject->loadCdEntry($number, $cdEntry);
	}
	public function loadEntries($number,$name,$elink) {
		$this->editObject->loadEntries($number,$name,$elink);
	}

	public function loadIds($haken,$name,$link) {
		$this->editObject->loadIds($haken,$name,$link);
	}

	public function loadJS($jsButton,$jsUrl) {
		$this->editObject->loadJavascript($jsButton,$jsUrl);
	}


	public function createdEditObject() {

		return $this->editObject;

		$this->clear();
	}
}

?>
