<?php

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
 */
function dd()
{
    if (class_exists('\Symfony\Component\VarDumper\Dumper\HtmlDumper')) {
        array_map(function ($x) {
            $dumper = new Dumper;
            $dumper->dump($x);
        }, func_get_args());
    } else {
        array_map(function ($x) {var_dump($x);}, func_get_args());
    }
    exit(1);
}

function de()
{
    array_map(function ($x) {echo $x;}, func_get_args());

    exit(1);
}

/*
|--------------------------------------------------------------------------
| Autoload
|--------------------------------------------------------------------------
 */

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \Composer\Autoload\ClassLoader;

$loader->register(true);

$loader->addPsr4('App\\', array(__DIR__ . '/../app'));

$loader->addPsr4('', array(__DIR__ . '/../packages'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
 */
$app = \Closet\Foundation\Application::create(__DIR__ . '/../');

/*
|--------------------------------------------------------------------------
| Bind services
|--------------------------------------------------------------------------
 */

$app->singleton('app', function ($ioc) {
    return $ioc;
});
$app->bind('Illuminate\Container\Container', 'app');
$app->bind('Psr\Container\ContainerInterface', 'app');

$app->singleton('router', function () {
    return new \Bramus\Router\Router();
});

$app->singleton('events', function ($ioc) {
    return new \Illuminate\Events\Dispatcher($ioc);
});

$app->singleton('request', function ($ioc) {
    return \Closet\Http\Request::createFromGlobals();
});
$app->bind('Closet\Http\AspectRequest', 'request');
$app->bind('Psr\Http\Message\ServerRequestInterface', 'request');

$app->singleton('response', function ($ioc) {
    return new \Closet\Http\Response;
});
$app->bind('Closet\Http\AspectResponse', 'response');
$app->bind('Psr\Http\Message\ResponseInterface', 'response');

// flash
$app->singleton('flash', function ($ioc) {
    return new \Tamtamchik\SimpleFlash\Flash();
});

// csrf
$app->singleton('csrf', function ($ioc) {
    return new \Closet\Session\Csrf;
});

$app->bind('validator', function ($ioc) {
    return new \Rakit\Validation\Validator;
});

$app->singleton('translator', function ($ioc) {

    $langPath = $ioc->langPath();

    $trans = new \Repack\Translation\Translator(
        new \Repack\Translation\FileLoader($langPath), $ioc['config']['app.locale']
    );

    $trans->setFallback($ioc['config']['app.fallback_locale']);

    $trans->addJsonPath(dirname($langPath));

    return $trans;
});

// database
$app->singleton('capsule', function ($ioc) {

    $capsule = new \Illuminate\Database\Capsule\Manager;

    $capsule->addConnection(
        $ioc['config']['database.connections'][$ioc['config']['database.default']]
    );

    // Set the event dispatcher used by Eloquent models... (optional)
    $capsule->setEventDispatcher($ioc['events']);

    // Make this Capsule instance available globally via static methods... (optional)
    $capsule->setAsGlobal();

    // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
    $capsule->bootEloquent();

    return $capsule;
});

$app->singleton('db', function ($ioc) {
    return $ioc['capsule']->getDatabaseManager();
});

$app->singleton('db.connection', function ($ioc) {
    return $ioc['capsule']->getConnection();
});

$app->singleton('session', function ($ioc) {
    // session_start();
    // $sessionHandler = 'native';
    $sessionHandler = null;

    $sessionHandler = new \Closet\Session\Store($sessionHandler);

    return $sessionHandler;
});

$app->singleton('view', function ($ioc) {

    $engine = new \Latte\Engine();

    $engine->setLoader(new \Latte\Loaders\FileLoader($ioc['config']['view.path']));

    $engine->setTempDirectory($ioc['config']['view.compiled']);

    $latteView = new \Closet\View\LatteView($engine);

    $latteView->addParam('app', $ioc['app']);

    $latteView->addParam('ioc', $ioc['app']);

    $latteView->addParam('router', $ioc['router']);

    $latteView->addParam('request', $ioc['request']);

    $latteView->addParam('session', $ioc['session']);

    $latteView->addParam('translator', $ioc['translator']);

    return $latteView;
});

/*
|--------------------------------------------------------------------------
| Register App Middleware
|--------------------------------------------------------------------------
 */
// $app->add(new \App\Middlewares\AppMiddleware);

/*
|--------------------------------------------------------------------------
| Register routes
|--------------------------------------------------------------------------
 */
require_once __DIR__ . '/../routes/routes.php';

/*
|--------------------------------------------------------------------------
| Boot services
|--------------------------------------------------------------------------
 */
// init PHP session
$app['session'];

$app['capsule']->bootEloquent();

\Illuminate\Pagination\Paginator::currentPathResolver(function () use ($app) {
    return $app['request']->url();
});
\Illuminate\Pagination\Paginator::currentPageResolver(function () use ($app) {
    return $app['request']->input('page', 1);
});

$app->singleton('user', function ($ioc) {
    $user = $ioc['session']->get('_login');

    if ($user && ($user = \App\Models\UsersModel::find($user))) {
        return $user;
    }
});

/*
|--------------------------------------------------------------------------
| Run app
|--------------------------------------------------------------------------
 */
$app['router']->run(function () use ($app) {

    $app['session']->setPreviousUrl($app['request']->uri());

    echo $app['response']->getBody();

    exit;
});
