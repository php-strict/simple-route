<?php
ini_set('display_errors', '1');
ini_set('error_reporting', (string) E_ALL);
date_default_timezone_set('Europe/Moscow');

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

use PhpStrict\SimpleRoute\Route;
use PhpStrict\SimpleRoute\ArrayStorage;

$routes = [
    '/' => [
        'title'     => 'Main page title',
        'callback'  => function () {
            return 'Main page callback result';
        },
    ],
    '/qwe' => [
        'title'     => 'Page qwe title',
        'callback'  => function () {
            return 'Page qwe callback result';
        },
    ],
    '/qwe/rty' => [
        
    ],
    '/qwe/rty/uio' => [
        
    ],
];

$path = $_SERVER['PATH_INFO'] ?? $_SERVER['ORIG_PATH_INFO'] ?? $argv[1] ?? '';

$result = Route::find($path, new ArrayStorage($routes));

if (null === $result) {
    echo '404 Page not found';
    exit(404);
}

/*
structure of $result for path '/qwe/param1/param2':
{
    entry: {
        key: '/qwe',
        data: [
            'title'     => 'Page qwe title',
            'callback'  => function () {
                return 'Page qwe callback result';
            }
        ]
    },
    params: ['param1', 'param2']
}
*/

//just output

echo '<h1>' . $result->entry->data['title'] . '</h1>';

if (isset($result->entry->data['callback'])) {
    echo $result->entry->data['callback']();
}

if (0 < count($result->params)) {
    echo '<ul>';
    foreach ($result->params as $param) {
        echo '<li>' . $param . '</li>';
    }
    echo '</ul>';
}
