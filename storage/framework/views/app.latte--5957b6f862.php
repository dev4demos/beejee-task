<?php
// source: \app.latte

use Latte\Runtime as LR;

class Template5957b6f862 extends Latte\Runtime\Template
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
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <title><?php
		if ($this->getParentName()) return get_defined_vars();
		$this->renderBlock('title', get_defined_vars());
?></title>
</head>

<body>
    
    <nav class="navbar navbar-expand-md navbar-dark bg-dark mb-4">
        <a class="navbar-brand" href="/">Beejee</a>
        <button class="navbar-toggler collapsed" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-collapse collapse" id="navbarCollapse" style="">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="/"><?php echo LR\Filters::escapeHtmlText($app->trans('Tasks')) /* line 23 */ ?>

                        <span class="sr-only">(<?php echo LR\Filters::escapeHtmlText($app->trans('current')) /* line 24 */ ?>)</span>
                    </a>
                </li>
            </ul>
            <form class="form-inline mt-2 mt-md-0" method="POST" action="lang">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo LR\Filters::escapeHtmlText($app->trans('Language')) /* line 32 */ ?> (<?php
		echo LR\Filters::escapeHtmlText($_locale) /* line 32 */ ?>)
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <button class="dropdown-item <?php
		if ($_locale == 'en') {
			?> active <?php
		}
		?>" type="submit" name="lang" value="en"><?php echo LR\Filters::escapeHtmlText($app->trans('English')) /* line 35 */ ?></button>
                            <div class="dropdown-divider"></div>
                            <button class="dropdown-item <?php
		if ($_locale == 'ru') {
			?> active <?php
		}
		?>" type="submit" name="lang" value="ru"><?php echo LR\Filters::escapeHtmlText($app->trans('Russian')) /* line 37 */ ?></button>
                        </div>
                    </li>
                    <li class="nav-item">
<?php
		if ($session->get('_login')) {
			?>                            <a class="btn btn-danger badge-pill" href="/logout?id=<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($session->get('_login'))) /* line 42 */ ?>"><?php
			echo LR\Filters::escapeHtmlText($app->trans('Log out')) /* line 42 */ ?></a>
<?php
		}
		else {
			?>                            <a class="btn btn-light badge-pill" href="/login"><?php echo LR\Filters::escapeHtmlText($app->trans('Log in')) /* line 44 */ ?></a>
<?php
		}
?>

                    </li>
                </ul>
            </form>
        </div>
    </nav>

    <div id="content">
        <div class="alert">
<?php
		/* line 56 */
		$this->createTemplate('/messages.latte', $this->params, "include")->renderToContentType('html');
?>
            
            <?php
		$this->renderBlock('content', get_defined_vars());
?>
        </div>
    </div>
    <div class="alert">
        <p class="text-center">
            Version 1.0.0 (2020)
        </p>
    </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</body>

</html><?php
		return get_defined_vars();
	}


	function blockTitle($_args)
	{
		
	}


	function blockContent($_args)
	{
		
	}

}
