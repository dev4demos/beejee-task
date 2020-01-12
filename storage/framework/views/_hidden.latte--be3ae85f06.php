<?php
// source: \_hidden.latte

use Latte\Runtime as LR;

class Templatebe3ae85f06 extends Latte\Runtime\Template
{

	function main()
	{
		extract($this->params);
?>
 
<input type="hidden" name="_method" value="POST">
<input type="hidden" name="_token" value="<?php echo LR\Filters::escapeHtmlAttr($app['csrf']->token()) /* line 3 */ ?>">
<?php
		if (isset($id)) {
			?> <input type="hidden" name="id" value="<?php echo LR\Filters::escapeHtmlAttr($id) /* line 4 */ ?>"> <?php
		}
?>

<?php
		return get_defined_vars();
	}

}
