<?php
// source: index.latte

use Latte\Runtime as LR;

class Template9735eb03cb extends Latte\Runtime\Template
{
	public $blocks = [
		'title' => 'blockTitle',
		'content' => 'blockContent',
	];

	public $blockTypes = [
		'title' => 'html',
		'content' => 'html',
	];


	function main()
	{
		extract($this->params);
?>

<?php
		if ($this->getParentName()) return get_defined_vars();
		$this->renderBlock('title', get_defined_vars());
?>

<?php
		$this->renderBlock('content', get_defined_vars());
		return get_defined_vars();
	}


	function prepare()
	{
		extract($this->params);
		if (isset($this->params['val'])) trigger_error('Variable $val overwritten in foreach on line 11, 18');
		if (isset($this->params['item'])) trigger_error('Variable $item overwritten in foreach on line 37');
		$this->parentName = '/app.latte';
		
	}


	function blockTitle($_args)
	{
		extract($_args);
		?> <?php echo LR\Filters::escapeHtmlText($_title ?: 'Application title') /* line 3 */ ?> <?php
	}


	function blockContent($_args)
	{
		extract($_args);
?>
    <div class="row">
        <div class="col-sm">
            <form class="form-inline my-2 my-lg-0" method="POST" action="/">
                <div class="input-group mb-3">
                    <select class="form-control form-control-sm" name="sortBy">
<?php
		$iterations = 0;
		foreach (['username', 'email', 'task_status'] as $val) {
			?>                            <option value="<?php echo LR\Filters::escapeHtmlAttr($val) /* line 12 */ ?>" <?php
			if ($request->input('sortBy') == $val) {
				?> selected="selected" <?php
			}
?> > 
                            <?php echo LR\Filters::escapeHtmlText($app->trans($val)) /* line 13 */ ?>

                            </option>
<?php
			$iterations++;
		}
?>
                    </select>
                    <select class="form-control form-control-sm" name="sortOrder">
<?php
		$iterations = 0;
		foreach (['descending', 'ascending'] as $val) {
			?>                            <option value="<?php echo LR\Filters::escapeHtmlAttr($val) /* line 19 */ ?>" <?php
			if ($request->input('sortOrder') == $val) {
				?> selected="selected" <?php
			}
?> > 
                            <?php echo LR\Filters::escapeHtmlText($app->trans($val)) /* line 20 */ ?>

                            </option>
<?php
			$iterations++;
		}
?>
                    </select>
                    <span>&nbsp;&nbsp;</span>
                    <div class="input-group-append">
                        <button class="btn btn-dark btn-sm"><?php echo LR\Filters::escapeHtmlText($app->trans('Sort')) /* line 26 */ ?></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm">
            <a class="btn btn-primary float-right" href="/tasks/create"><?php echo LR\Filters::escapeHtmlText($app->trans('Create task')) /* line 32 */ ?></a>
        </div>&nbsp;
    </div>
    <!-- list -->
    <div>
<?php
		$iterations = 0;
		foreach ($items as $item) {
?>
            <div class="card">
                <div class="card-header">
                    <span class="badge badge-primary badge-pill"><?php echo LR\Filters::escapeHtmlText($item->getKey()) /* line 40 */ ?></span>
                    <span class="card-title"><?php echo LR\Filters::escapeHtmlText($item->task_text) /* line 41 */ ?></span>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-horizontal-md">

<?php
			if ($app['user']) {
?>
                        <li class="list-group-item">
                            <a class="btn btn-info btn-sm" href="/tasks/create?id=<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($item->id)) /* line 48 */ ?>"><?php
				echo LR\Filters::escapeHtmlText($app->trans('Edit task')) /* line 48 */ ?></a>
                        </li>
<?php
			}
?>

                        <li class="list-group-item"><strong><?php echo LR\Filters::escapeHtmlText($app->trans('username')) /* line 52 */ ?>:</strong></li>
                        <li class="list-group-item"><?php echo LR\Filters::escapeHtmlText($item->username) /* line 53 */ ?></li>
                        <li class="list-group-item"><strong><?php echo LR\Filters::escapeHtmlText($app->trans('task_status')) /* line 54 */ ?>:</strong></li>
<?php
			if ($item->task_status == 'complete') {
?>
                            <li class="list-group-item">
                                <span class="btn btn-success btn-sm"> <?php echo LR\Filters::escapeHtmlText($app->trans($item->task_status)) /* line 57 */ ?> </span>
                            </li>
<?php
			}
			else {
				?>                            <li class="list-group-item"><?php echo LR\Filters::escapeHtmlText($app->trans($item->task_status)) /* line 60 */ ?></li>
<?php
			}
?>
                    </ul>
                </div>
                <div class="card-footer">
                    <small class="text-muted"><?php echo LR\Filters::escapeHtmlText($app->trans('Last updated by')) /* line 65 */ ?>&nbsp;<strong><?php
			echo LR\Filters::escapeHtmlText($item->updated_by) /* line 65 */ ?></strong></small>
                </div>
            </div>
            <br>
<?php
			$iterations++;
		}
?>
    </div>
    <!-- pager -->
    <nav aria-label="...">
        <?php echo $items->render(new \App\Pagination\BootstrapFourPresenter($items)) /* line 73 */ ?>

    </nav>
<?php
	}

}
