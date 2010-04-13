		'<?=$field->name;?>' => new Field_<?=$field->type;?>
<? if( count($field->getOptions()) > 0 ) : ?>(array(
<? foreach( $field->getOptions() as $key => $val ) : ?>
			'<?=$key;?>' => <?=Arr::write($val, TRUE);?>,
<? endforeach; ?>
		))<? endif; ?>,
