# Simple route

[![Software License][ico-license]](LICENSE.txt)
[![Build Status][ico-travis]][link-travis]
[![codecov][ico-codecov]][link-codecov]
[![Codacy Badge][ico-codacy]][link-codacy]

Simple request router. All routes is a key/entry pairs.
Router looking for entry closest to key, and returns it 
with remainder of searching key as parameters array (splited by slash).

It can be used to delegate execution from core to standalone modules.
Each module takes remainder of searching key as parameters array 
and use it by its own.

Storage example:

```php
<?php
return [
    '/'         => ['some callback, module class, ... here'],
    '/qwe'      => ['some callback, module class, ... here'],
    '/asd'      => ['some callback, module class, ... here'],
    '/qwe/rty'  => ['some callback, module class, ... here'],
    '/asd/fgh'  => ['some callback, module class, ... here'],
];
```

For path '/qwe/param1/param2' route returns second entry and parameters array (param1, param2).

## Supported storages

*   array (supports callbacks)
*   file (supports callbacks),
*   SQLite,
*   MySQL (uses main db connection from app).

## Requirements

*   PHP >= 7.1

## Install

Install with [Composer](http://getcomposer.org):
    
```bash
composer require php-strict/simple-route
```

## Usage

Basic usage:

```php
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
        'title'     => 'Page qwe/rty title',
    ],
    '/qwe/rty/uio' => [
        'title'     => 'Page qwe/rty/uio title',
    ],
];

$path = $_SERVER['PATH_INFO'] ?? $_SERVER['ORIG_PATH_INFO'];

$result = Route::find($path, new ArrayStorage($routes));

if (null === $result) {
    //show error or redirect to mainpage
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
```

## Tests

To execute the test suite, you'll need [Codeception](https://codeception.com/).

```bash
vendor\bin\codecept run
```

[ico-license]: https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/php-strict/simple-route/master.svg?style=flat-square
[link-travis]: https://travis-ci.org/php-strict/simple-route
[ico-codecov]: https://codecov.io/gh/php-strict/simple-route/branch/master/graph/badge.svg
[link-codecov]: https://codecov.io/gh/php-strict/simple-route
[ico-codacy]: https://api.codacy.com/project/badge/Grade/35b8cef91ae049d6b92bf270a119b877
[link-codacy]: https://www.codacy.com/app/php-strict/simple-route?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=php-strict/simple-route&amp;utm_campaign=Badge_Grade
