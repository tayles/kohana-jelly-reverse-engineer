<?php defined('SYSPATH') or die('No direct script access.');

class Arr extends Kohana_Arr {

	public static function write($arr, $recursive = FALSE) {
		if( !is_array($arr) ) return self::nulltruefalse($arr);
		
		$html = 'array(';
		$is_assoc = self::is_assoc($arr);
		if( $is_assoc ) {
			$vals = array();
			foreach( $arr as $key => $val ) {
				$vals[] = "'{$key}' => " . self::nulltruefalse($val);
			}
			$html .= implode(', ', $vals);
		}
		else {
			$html .= "'" . implode( "', '", $arr ) . "'";
		}
		$html .= ')';
		
		return $html;
	}
	
	private static function nulltruefalse($val) {
		return ( is_null($val) ? 'NULL' : ( $val === TRUE ? 'TRUE' : ( $val === FALSE ? 'FALSE' : "'{$val}'" ) ) );
	}

}
