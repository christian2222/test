<?php


require_once 'Adt.php';
class ArrayList extends Adt implements Iterator,Countable {

	protected $arr = null;

	public function __construct() {
		$this->arr = array();
	}

	
	public function toArray() {
		return $this->arr;
	}
	
	public function loadArray($arr) {
		$this->arr = $arr;
	}
	
	public function add($obj) {
		$this->arr[] = $obj;
	}

	public function remove_by_index($e_index) {
		if(array_key_exists($e_index,$this->arr)) {
			array_splice($this->arr,$e_index,1);
			return true;
		}
		else {
			//throw new Exception('Index our of bound error');
			return null;
		}

	}


	public function get_by_index($e_index) {
		if(array_key_exists($e_index,$this->arr)) {
			return $this->arr[$e_index];
		}
		else {
			//throw new Exception('Index our of bound error');
			return null;
		}
	}

	public function change_by_index($e_index,$new_obj) {
		if(array_key_exists($e_index,$this->arr)) {
			$this->arr[$e_index] = $new_obj;
			return true;
		}
		else {
			//throw new Exception('Index our of bound error');
			return null;
		}

	}

	public function change_by_object($obj,$new_obj) {
		$index = array_search($obj,$this->arr);
		if( $index === false) {
			//throw new Exception('Object not found');
			return null;
		}
		else {
			$this->arr[$index]=$new_obj;
			return true;
		}
	}


	public function remove_by_object($obj) {
		$index = array_search($obj,$this->arr);
		if( $index === false) {
			//throw new Exception('Object not found');
			return null;
		}
		else {
			array_splice($this->arr,$index,1);
		}
	}

	public function to_array() {
		return $this->arr;
	}

	public function is_empty() {
		return empty($this->arr);
	}

	// implementation of Iterator-Interface
	
	
	protected $index = 0;

	public function current() {
		return $this->arr[$this->index];
	}

	public function next() {
		$this->index++;
	}

	public function rewind() {
		$this->index = 0;
	}

	public function key() {
		return $this->index;
	}

	public function valid() {
		return $this->index < count($this->arr);
	}

	// Interface Countable

	public function count() {
		return count($this->arr);
	}

	public function __toString() {
		$string = '';
		for($this->rewind(); $this->valid(); $this->next()) {
			$entry = $this->current();
			$string .= $entry->__toString();
		}
	}


}


?>
