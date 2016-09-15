<?php

require_once 'Adt.php';
require 'Presenter.php';

class CdEntry extends Adt implements Presenter {

	protected $title;
	protected $textLink;

	public function __construct($title,$link) {
		$this->title = $title;
		$this->textLink = $link;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	public function setLink($link) {
		$this->textLink = $link;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getLink() {
		return $this->textLink;
	}



	public function __toString() {
		return $this->title . '|' . $this->textLink;
	}

	public function present() {
		return '<tr><td>' . $this->title . '</td><td>' . $this->textLink . '</td></tr>';
	}

	public function edit() {
		return '<input value="' . $this->title . '" /><input value="' .$this->textLink.'" />';
	}
}
?>
