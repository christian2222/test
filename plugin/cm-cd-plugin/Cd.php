<?php

require 'ArrayList.php';
require 'CdEntry.php';
require 'Adt.php';
// require 'Presenter.php'; // not yet implemented

class Cd extends Adt implements Presenter {
	protected $entires = new ArrayList();
	protected $name;

	//public add($entry) {
	//	$this->entries->add($entry)
	//}

	public function __construct($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getEntries() {
		// referenz !!!
		return $this->entries;
	}


	public function __toString() {
		$ret = '';
		$ret .= $this->name . '<br>';
		for($this->entries->rewind(); $this->entries->valid(); $this->entries->next()) {
			$ret .= $this->entries->current()->__toString(). '<br>';
		}

		return $ret;
	}
	
	public function present(){
		$string = '';
		$string .= '';
		$string .= '';
		
	}

}

?>
