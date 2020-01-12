<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------|
 */
$app['router']->set404(function () use ($app) {
    // header('HTTP/1.1 404 Not Found');
    header("Location: /");
    exit();
});
// middleware
$app['router']->before('GET', '/.*', function () use ($app) {

    $schema = $app['db']->connection()->getSchemaBuilder();

    // check if tables exists
    if (!$schema->hasTable('tasks') && !$app['request']->is('installer*')) {
        header("Location: /installer/install");
        exit();
    }

    // protect pages user can only visit when loged in
    if ($app['session']->get('_login') &&
        $app['request']->is('login*', 'register*')) {
        header("Location: /");
        exit();
    }

    // check for csrf token on every post
    if ($app['request']->isMethod('POST') && !$app['csrf']->post()) {
        header("Location: /");
        exit();
    }
});

$app['router']->get('/installer/(\w+)', function ($action) use ($app) {

    $migration = new \App\Migrations\AppMigration($app);

    if (in_array($action, ['up', 'install'])) {
        $migration->up();
    } elseif (in_array($action, ['down', 'uninstall'])) {
        $migration->down();
    } elseif (in_array($action, ['tidy'])) {
        $migration->down();
        $migration->up();
    }

    return $app['response']->redirectTo('/');
});

$app['router']->match('POST', '/lang', function () use ($app) {
    return call_user_func_array([$app['\App\Controllers\TasksController'], 'lang'], func_get_args());
});

// home

$app['router']->match('GET|POST', '/', function () use ($app) {
    return call_user_func_array([$app['\App\Controllers\TasksController'], 'index'], func_get_args());
});

$app['router']->match('GET|POST', '/tasks', function () use ($app) {
    return call_user_func_array([$app['\App\Controllers\TasksController'], 'index'], func_get_args());
});

$app['router']->get('/tasks/create', function () use ($app) {
    return call_user_func_array([$app['\App\Controllers\TasksController'], 'create'], func_get_args());
});

$app['router']->match('POST', '/tasks/store', function () use ($app) {
    return call_user_func_array([$app['\App\Controllers\TasksController'], 'store'], func_get_args());
});

$app['router']->match('GET|POST', '/tasks/destroy', function () use ($app) {
    return call_user_func_array([$app['\App\Controllers\TasksController'], 'destroy'], func_get_args());
});

// auth

$app['router']->match('GET|POST', '/login', function () use ($app) {
    return call_user_func_array([$app['\App\Controllers\AuthController'], 'login'], func_get_args());
});

$app['router']->match('GET|POST', '/logout', function () use ($app) {
    return call_user_func_array([$app['\App\Controllers\AuthController'], 'logout'], func_get_args());
});

// end

// $app['router']->get('/hello/(\w+)', function ($name) use ($app) {

//     $response = $app['response'];

//     $response->getBody()->write('Hello ' . htmlentities($name));

//     return $response;
// });

// $app['router']->mount('/movies', function () use ($app) {

//     // will result in '/movies/'
//     $app['router']->get('/', function () use ($app) {
//         $response = $app['response'];

//         $response->getBody()->write('movies overview');

//         return $response;
//     });

//     // will result in '/movies/id'
//     $app['router']->get('/(\d+)', function ($id) use ($app) {
//         return 'movie id ' . htmlentities($id);
//     });
// });
