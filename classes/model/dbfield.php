<?php defined('SYSPATH') or die('No direct script access.');

class Model_DBField extends Model {

	public $name, $type;
	
	public $options = array(), $enum_choices = array(), $rules = array(), $filters = array();
	
	public function hasOptions() {
	
	}
	
	public function getOptions() {
		switch( $this->type ) {
			case 'Enum':
			case 'Set':
				$this->options['choices'] = $this->enum_choices;
				break;
		}
		
		if( count($this->rules) > 0 ) $this->options['rules'] = $this->rules;
		if( count($this->filters) > 0 ) $this->options['filters'] = $this->filters;
		
		
		
		return $this->options;
	}

}
