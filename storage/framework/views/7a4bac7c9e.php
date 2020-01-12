<?php
// source: create.latte

use Latte\Runtime as LR;

class Template7a4bac7c9e extends Latte\Runtime\Template
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
		if (isset($this->params['val'])) trigger_error('Variable $val overwritten in foreach on line 15, 47');
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
    <!-- detai -->
    <div class="row">
        <div class="col-sm">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo LR\Filters::escapeHtmlText($app->trans('Task Detail')) /* line 11 */ ?></h5>
                </div>
                <div class="card-body">
                    <datalist id="username-list">
<?php
		$iterations = 0;
		foreach ($_usernameList as $val) {
			?>                            <option value="<?php echo LR\Filters::escapeHtmlAttr($val) /* line 16 */ ?>"><?php
			echo LR\Filters::escapeHtmlText($val) /* line 16 */ ?></option>
<?php
			$iterations++;
		}
?>
                    </datalist>
                    <form action="store" method="POST">
<?php
		/* line 20 */
		$this->createTemplate('/_hidden.latte', $this->params, "include")->renderToContentType('html');
?>
                        
                        <div class="form-group">
                            <label for="username"><?php echo LR\Filters::escapeHtmlText($app->trans('Username')) /* line 23 */ ?></label>
                            <input type="text" name="username" class="form-control" id="username" list="username-list" aria-describedby="username-help"
                            value="<?php echo LR\Filters::escapeHtmlAttr(isset($username) ? $username : $session->getOldInput('username')) /* line 25 */ ?>">
<?php
		if (isset($_errors['username'])) {
			?>                                <small id="username-help" class="form-text text-muted"> <?php echo LR\Filters::escapeHtmlText($_errors['username']) /* line 28 */ ?> </small>
<?php
		}
?>
                        </div>
                        <div class="form-group">
                            <label for="example"> <?php echo LR\Filters::escapeHtmlText($app->trans('Email address')) /* line 32 */ ?> </label>
                            <input type="email" name="email" class="form-control" id="example" aria-describedby="email-help"
                            value="<?php echo LR\Filters::escapeHtmlAttr(isset($email) ? $email : $session->getOldInput('email')) /* line 34 */ ?>">
                        </div>
                        <div class="form-group">
                            <label for="task_text"> <?php echo LR\Filters::escapeHtmlText($app->trans('Task text')) /* line 38 */ ?> </label>
                            <textarea name="task_text" class="form-control" id="task_text" rows="2" aria-describedby="task_text-help"><?php
		echo LR\Filters::escapeHtmlText(isset($task_text) ? $task_text : $session->getOldInput('task_text')) /* line 39 */ ?> </textarea>
                        </div>

<?php
		if ($session->get('_login')) {
?>
                            <div class="form-group">
                                <label for="task_status"> <?php echo LR\Filters::escapeHtmlText($app->trans('Task status')) /* line 45 */ ?> </label>
                                <select class="form-control" name="task_status" id="task_status">
<?php
			$iterations = 0;
			foreach ($_task_statusList as $val) {
				?>                                        <option value="<?php echo LR\Filters::escapeHtmlAttr($val) /* line 48 */ ?>" 
                                        <?php
				if ((empty($task_status) ? $session->getOldInput('task_status') : $task_status) === $val) {
					?> selected="selected" <?php
				}
?>> 
                                        <?php echo LR\Filters::escapeHtmlText($app->trans($val)) /* line 50 */ ?>

                                        </option>
<?php
				$iterations++;
			}
?>
                                </select>
                            </div>
<?php
		}
?>
                        
                        <div class="float-left">
<?php
		if ($session->get('_login')) {
			if (isset($id)) {
				?>                                    <a class="btn btn-danger" href="/tasks/destroy?id=<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($id)) /* line 60 */ ?>"> <?php
				echo LR\Filters::escapeHtmlText($app->trans('Delete')) /* line 60 */ ?> </a>
<?php
			}
		}
?>

                            <button type="reset" class="btn btn-warning"> <?php echo LR\Filters::escapeHtmlText($app->trans('Reset Changes')) /* line 64 */ ?> </button>
                            <a class="btn btn-light" href="/"> <?php echo LR\Filters::escapeHtmlText($app->trans('Cancel')) /* line 65 */ ?> </a>
                        </div>
                        <div class="float-right">
                            <button type="submit" class="btn btn-primary"> <?php echo LR\Filters::escapeHtmlText($app->trans('Save')) /* line 68 */ ?> </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
	}

}
