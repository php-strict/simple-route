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
 * Simple request router.
 * 
 * Looking for entry in routes storage.
 */
class Route implements RouteInterface
{
    /**
     * Looking for entry in routes storage according to path and return result.
     * 
     * @param string $path
     * @param \PhpStrict\SimpleRoute\StorageInterface $storage
     * 
     * @return \PhpStrict\SimpleRoute\RouteResult|null
     */
    public static function find(string $path, StorageInterface $storage): ?RouteResult
    {
        $entry = null;
        try {
            $entry = static::getEntry($path, $storage);
        } catch (NotFoundException $e) {
            return null;
        }
        
        $params = [];
        //strlen faster than simple comparison
        $keylen = strlen($entry->key);
        if (strlen($path) > $keylen) {
            $params = explode('/', trim(substr($path, $keylen), '/'));
        }
        
        return new RouteResult($entry, $params);
    }
    
    /**
     * Gets route storage entry.
     * 
     * @param string $path
     * @param \PhpStrict\SimpleRoute\StorageInterface $storage
     * 
     * @return StorageEntry
     * 
     * @throws \PhpStrict\SimpleRoute\NotFoundException
     */
    protected static function getEntry(string $path, StorageInterface $storage): StorageEntry
    {
        if ('' == $path || '/' == $path) {
            return $storage->get('/');
        }
        
        return $storage->find($path);
    }
}
