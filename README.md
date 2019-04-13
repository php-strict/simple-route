# Simple route

[![Software License][ico-license]](LICENSE.txt)

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

For path '/qwe/param1/param2' route returns second entry and parameters array [param1, param2].

## Supported storages:

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
```

## Tests

To execute the test suite, you'll need [Codeception](https://codeception.com/).

```bash
vendor\bin\codecept run
```

[ico-license]: https://img.shields.io/badge/license-GPL-brightgreen.svg?style=flat-square
