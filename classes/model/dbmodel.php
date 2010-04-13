<?php defined('SYSPATH') or die('No direct script access.');

class Model_DBModel extends Model {

	public $table, $fields;
	
	public function filename() {
		return strtolower($this->name()) . '.php';
	}
	
	public function name() {
		// convert table name to a valid model name
		// e.g. guides_pubs -> GuidePub
		
		// remove underscores and dashes
		$name_parts = explode(' ', Inflector::humanize($this->table));
		
		// singularize each bit
		$name_parts = array_map('Inflector::singular', $name_parts);
		
		// combine + camelize		
		return ucwords(Inflector::camelize(implode(' ', $name_parts)));
	}

}
