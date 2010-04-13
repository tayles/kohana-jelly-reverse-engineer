<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<title>Jelly Forward-Engineered Models</title>
<style type="text/css">
body {
	font-family: sans-serif;
	color: #333;
	margin: 1em;
}

textarea {
	width: 90%;
	font-family: monospace;
	font-size: 9pt;
	border: 2px solid #333;
	padding: 1em;
}
</style>
<script type="text/javascript">
window.onload = function() {
	var code_frags = document.getElementsByTagName('textarea');
	for( var i = 0; i < code_frags.length; i++ ) {
		code_frags[i].onclick = function() {
			this.select();
		}
	}
	code_frags[0].select();
}
</script>
</head>
<body>

<h1>Jelly Models</h1>

<?=Html::anchor('jellyforwardengineer/downloadModels', 'Download all models (jelly_models.zip)');?> 
<small><em>Requires Archive module from <?=Html::anchor('http://github.com/zazu/kohana-archive');?></em></small>

<div>
<p>Generated models:</p>
<ul>
<? foreach( $models as $model ) : ?>
<li><?=Html::anchor('#' . $model->name(), $model->filename());?></li>
<? endforeach; ?>
</ul>
</div>

<? foreach( $models as $model ) : ?>
<h2 id=<?=$model->name();?>><?=$model->filename();?></h2>
<!--pre>
<? foreach( $model->fields as $field ) : ?>
<?=Kohana::debug($field->raw_data);?>
<? endforeach; ?>
</pre-->

<? $view = View::factory('jelly_model_skeleton')->set('escape',TRUE)->set('model',$model)->render(); ?>
<textarea rows="<?=substr_count($view, "\n");?>">
<?=$view;?>
</textarea>
<? endforeach; ?>

</body>
</html>