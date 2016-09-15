<?php

class EditObject {

	public function __construct() {
		
	}
	
	protected $number = 0;
	protected $name = '';
	protected $olink = '';

	public function loadCdEntry($number,$cdEntry) {
		$this->number = $number;
		$this->name = $cdEntry->getTitle();
		$this->olink = $cdEntry->getLink();
	}
	public function loadEntries($number, $name,$olink) {
		$this->number = $number;
		$this->name = $name;
		$this->olink = $olink;
	}

	protected $idName = '';
	protected $idLink ='';
	protected $idHaken ='';



	public function loadIds($haken,$name,$link) {
		$this->idName = $name;
		$this->idLink = $link;
		$this->idHaken = $haken;
	}

	protected $jsButton='';
	protected $jsUrl='';

	public function loadJavascript($button,$url) {
		$this->jsButton = $button;
		$this->jsUrl = $url;
	}

	public function createJavascript() {
		$string = '';
		$string .= '<script type="text/javascript">';
		$string .= "jQuery(document).ready(function($){";
		$string .= "	$(#".$this->jsButton."').click(function(e) {";
		$string .= "		e.preventDefault();";
		$string .= "		var file = wp.media({ title: 'MP3-Datei aus Mediathek wählen', multiple: false }).open().on('select', function(e){";
		$string .= "			var uploadfile = file.state().get('selection').first();";
		$string .= "			console.log(uploadfile);";
		$string .= "			var fileurl = uploadfile.toJSON().url;";
		$string .= "			$('#".$this->jsUrl."').val(fileurl);";
		$string .= "		});"; //wp.media
		$string .= "	});"; //$(mp3btn)
		$string .= "});"; //jQuery
		$string .= '</script>';
	
	
		return $string;
	}	
	
	public function createCdEntry() {
		$string = '';
		$string .= '<p>';
		$string .= '<input type="checkbox" name='.$this->idHaken.' checked />';
		$string .= $this->number.'. Titel: <input type="text" name="'.$this->idName.'" value="'.$this->name.'" class="regular-text" /><br>';
		$string .= 'MP3-Link: <input type="text" id="'.$this->jsUrl.'" name="'.$this->idLink.'" size="50" value="'.$this->olink.'" class="regular-text" />'; // ???
		$string .= '<input type="button" id="'.$this->jsButton.'" class="button-secondary'.$this->number.'" value="Aus Mediathek..." />';
		$string .= '</p>';
		
		$string .= $this->createJavascript();

		return $string;

	}




}

?>
