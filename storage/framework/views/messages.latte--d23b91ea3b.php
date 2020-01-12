<?php
// source: \\messages.latte

use Latte\Runtime as LR;

class Templated23b91ea3b extends Latte\Runtime\Template
{

	function main()
	{
		extract($this->params);
		if ($_errors) {
?><div class="alert alert-danger alert-dismissible fade show" role="alert" role="alert">
    <?php
			$iterations = 0;
			foreach ($iterator = $this->global->its[] = new LR\CachingIterator($_errors) as $msg) {
?>

        <div><small id="error-<?php echo LR\Filters::escapeHtmlAttr($iterator->counter) /* line 3 */ ?>"><?php
				echo LR\Filters::escapeHtmlText($msg) /* line 3 */ ?></small></div>
<?php
				$iterations++;
			}
			array_pop($this->global->its);
			$iterator = end($this->global->its);
?>
          
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    <span aria-hidden="true">&times;</span>
	 </button>     
</div>
<?php
		}
?>

<?php
		if ($_success) {
?><div class="alert alert-success alert-dismissible fade show" role="alert" role="alert">
    <?php
			$iterations = 0;
			foreach ($iterator = $this->global->its[] = new LR\CachingIterator($_success) as $msg) {
?>

        <div><small id="error-<?php echo LR\Filters::escapeHtmlAttr($iterator->counter) /* line 13 */ ?>"><?php
				echo LR\Filters::escapeHtmlText($msg) /* line 13 */ ?></small></div>
<?php
				$iterations++;
			}
			array_pop($this->global->its);
			$iterator = end($this->global->its);
?>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    <span aria-hidden="true">&times;</span>
	 </button>
</div><?php
		}
		return get_defined_vars();
	}


	function prepare()
	{
		extract($this->params);
		if (isset($this->params['msg'])) trigger_error('Variable $msg overwritten in foreach on line 2, 12');
		
	}

}
