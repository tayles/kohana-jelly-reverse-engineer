<?php defined('SYSPATH') or die('No direct script access.');

class Controller_JellyReverseEngineer extends Controller {
	
	// mysql datatype to Jelly field type mappings (adapted from core Database and MySQL driver code)
	private $datatype_map = array(
			'bit'						=>'String',
			'bit varying'				=>'String',
			'char'						=>'String',
			'char varying'				=>'String',
			'character'					=>'String',
			'character varying'			=>'String',
			'date'						=>'Timestamp',
			'dec'						=>'Float',
			'decimal'					=>'Float',
			'double precision'			=>'Float',
			'float'						=>'Float',
			'int'						=>'Integer',
			'integer'					=>'Integer',
			'interval'					=>'String',	// ?
			'national char'				=>'String',
			'national char varying'		=>'String',
			'national character'		=>'String',
			'national character varying'=>'String',
			'nchar'						=>'String',
			'nchar varying'				=>'String',
			'numeric'					=>'Float',
			'real'						=>'Float',
			'smallint'					=>'Integer',
			'time'						=>'Timestamp',
			'time with time zone'		=>'Timestamp',
			'timestamp'					=>'Timestamp',
			'timestamp with time zone'	=>'Timestamp',
			'varchar'					=>'String',

			// SQL:1999
			'binary large object'		=>'Text',
			'blob'						=>'Text',
			'boolean'					=>'Boolean',
			'char large object'			=>'Text',
			'character large object'	=>'Text',
			'clob'						=>'Text',
			'national character large object'	=>'Text',
			'nchar large object'		=>'Text',
			'nclob'						=>'Text',
			'time without time zone'	=>'Timestamp',
			'timestamp without time zone'	=>'Timestamp',

			// SQL:2003
			'bigint'					=>'Integer',

			// SQL:2008
			'binary'					=>'Text',
			'binary varying'			=>'Text',
			'varbinary'					=>'Text',
			
			'blob'						=>'Text',
			'bool'						=>'Boolean',
			'bigint unsigned'			=>'Integer',
			'datetime'					=>'Timestamp',
			'decimal unsigned'			=>'Float',
			'double'					=>'Float',
			'double precision unsigned'	=>'Float',
			'double unsigned'			=>'Float',
			'enum'						=>'Enum',
			'fixed'						=>'Float',
			'fixed unsigned'			=>'Float',
			'float unsigned'			=>'Float',
			'int unsigned'				=>'Integer',
			'integer unsigned'			=>'Integer',
			'longblob'					=>'Text',
			'longtext'					=>'Text',
			'mediumblob'				=>'Text',
			'mediumint'					=>'Integer',
			'mediumint unsigned'		=>'Integer',
			'mediumtext'				=>'Text',
			'national varchar'			=>'String',
			'numeric unsigned'			=>'Float',
			'nvarchar'					=>'String',
			'real unsigned'				=>'Float',
			'set'						=>'Enum',
			'smallint unsigned'			=>'Integer',
			'text'						=>'Text',
			'tinyblob'					=>'String',
			'tinyint'					=>'Integer',
			'tinyint unsigned'			=>'Integer',
			'tinytext'					=>'String',
			'year'						=>'Timestamp',
		);
		
		
	public function action_index() {
	
		$models = $this->_generateModels();
		
		echo View::factory('jelly_model_list')
					->set('models', $models)
					->render();
	}
	
	public function action_downloadModels() {
	
		$models = $this->_generateModels();
		
		// requires fork of KO3 Archive module from Ralf Blumenthal <http://github.com/zazu/kohana-archive> which contains the add_content() method
		$archive = Archive::factory('zip');
		
		foreach( $models as $model ) {		
			$model_contents = View::factory('jelly_model_skeleton')->set('model', $model)->render();
			
			$archive->add_content($model->filename(), $model_contents);
		}
		
		// output archive data
		$this->request->response = $archive->save();
		
		// stream request as a download
		$this->request->send_file(TRUE, 'jelly_models.zip');
	}
	
	private function _generateModels() {					
		
		// assume default database config
		$tables = Database::instance()->list_tables();
		
		$models = array();
		
		foreach( $tables as $table ) {
			
			// model is a very simple intermediate container for our fields and is NOT a Jelly model
			$model = new Model_DBModel();
			$model->table = $table;
			
			$columns = Database::instance()->list_columns($model->table);
			
			// if the table is innodb, determine the foreign key references, otherwise guess them
			if( FALSE ) $foreign_key_check_mode = 'reference';
			else $foreign_key_check_mode = 'guess';
			
			
			foreach( $columns as $column ) {
			
				// column contains raw information about the column and its properties
				//echo '<pre>' . Kohana::debug($column) . '</pre>';
			
				$field = new Model_DBField();
				$field->raw_data = $column;
				
				$field->name = $column['column_name'];
				
				// map the data type to built-in Jelly field types
				$field->type = $this->datatype_map[$column['data_type']];
				
				if( $field->name == 'id' ) {
					// assume primary key with no options
					$field->type = 'Primary';
					$model->fields[$field->name] = $field;
					continue;
				}
				else {
					// check for foreign keys
					if( $foreign_key_check_mode == 'guess' ) {
						if( $prefix = StringManip::is_or_ends_with($field->name, 'id') ) {
							$matched_table = null;
							if( in_array( $prefix, $tables ) ) $matched_table = $prefix;
							else if( in_array( Inflector::plural($prefix), $tables ) ) $matched_table = Inflector::plural($prefix);
							
							if( $matched_table ) {
								// we have a possible foreign key here
								$field->type = 'BelongsTo';
								$field->name = $prefix;
								
								// add it to the table now and don't run any further rules
								$model->fields[$field->name] = $field;
								continue;
							}
						}
					}
					else {
						// TODO - use foreign key lookups
					}
				}
				
				// enumerate values
				if( isset($column['values']) ) $field->enum_choices = $column['values'];
				
				
				// assume tinyint(1) fields are boolean
				if( strpos($column['data_type'], 'tinyint') !== FALSE && isset($column['character_maximum_length']) && $column['character_maximum_length'] == 1 ) {
					$field->type = 'Boolean';
					unset($column['character_maximum_length']);
					unset($column['is_nullable']);
				}
				
				// apply a default value if it isn't NULL, 0 or FALSE
				if( isset($column['column_default']) && $column['column_default'] ) {
					$field->options['default'] = $column['column_default'];
				}
				
				// apply max length if less than the default for that datatype (i.e. 255 for varchars)
				if( isset($column['character_maximum_length']) && ( $column['type'] != 'string' || $column['character_maximum_length'] < 255 ) ) {
					$field->rules['max_length'] = $column['character_maximum_length'];
				}
				
				if( isset($column['is_nullable']) && $column['is_nullable'] === FALSE ) {
					$field->rules['not_empty'] = NULL;
				}
				
				// attempt to map email fields to the correct field type (Jelly_Field_Email)
				if( StringManip::is_or_ends_with($field->name, 'email') ) {
					$field->type = 'Email';
				}
				
				// using Text classname instead of Text, as subclassing Text in a module didn't seem to work (possibly a bug?)
				if( StringManip::is_or_ends_with($field->name, array('url','website') ) ) {
					$field->rules['url'] = NULL;
				}
				
				if( StringManip::is_or_ends_with($field->name, array('ip','ipaddress','ip_address') ) ) {
					$field->rules['ip'] = NULL;
				}
				
				if( StringManip::is_or_ends_with($field->name, array('telephone', 'tel', 'phone') ) ) {
					$field->rules['phone'] = NULL;
				}
				
				// if the column is named e.g. date_created, creation_date etc then set it to auto
				if( $field->type == 'Timestamp' && StringManip::contains($field->name, array('creation', 'created', 'added') ) ) {
					$field->options['auto_now_create'] = TRUE;
				}
				
				if( $field->type == 'Timestamp' && StringManip::contains($field->name, array('edited', 'modified', 'updated') ) ) {
					$field->options['auto_now_update'] = TRUE;
				}
				
				$model->fields[$field->name] = $field;
			}
			
			$models[] = $model;
		
		}
		
		return $models;
		
	}
	
}