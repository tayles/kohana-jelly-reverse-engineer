<?=(isset($escape) ? '&lt;' : '<');?>?php defined('SYSPATH') or die('No direct script access.');

class Model_<?=$model->name();?> extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        $meta->table('<?=$model->table;?>')
             ->fields(array(
<? foreach( $model->fields as $field ) : ?>
<?=View::factory('jelly_field_skeleton')->set('field',$field)->render();?>
<? endforeach; ?>
             ));
    }
}
