<?php
/**
 * PHP Strict.
 * 
 * @copyright   Copyright (C) 2018 - 2019 Enikeishik <enikeishik@gmail.com>. All rights reserved.
 * @author      Enikeishik <enikeishik@gmail.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace PhpStrict\SimpleRoute;

/**
 * Simple request router interface. 
 * 
 * Looking for entry in routes storage.
 */
interface RouteInterface
{
    /**
     * Looking for entry in routes storage according to path and return result.
     * 
     * @param string $path
     * @param \PhpStrict\SimpleRoute\StorageInterface $storage
     * 
     * @return \PhpStrict\SimpleRoute\RouteResult|null
     */
    public static function find(string $path, StorageInterface $storage): ?RouteResult;
}
