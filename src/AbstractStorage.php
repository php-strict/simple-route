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

abstract class AbstractStorage implements StorageInterface
{
    /**
     * Split path and create array of paths with all parts.
     * "/qwe/asd/zxc" -> ["/qwe/asd/zxc", "/qwe/asd", "/qwe"]
     * 
     * @param string $path
     * @return array
     */
    protected function getPaths(string $path): array
    {
        $pathParts = explode('/', trim($path, '/'));
        $pathPartsCount = count($pathParts);
        
        $paths = array('');
        for ($i = 0; $i < $pathPartsCount; $i++) {
            $paths[$i + 1] = $paths[$i] . '/' . $pathParts[$i];
        }
        array_shift($paths);
        
        rsort($paths, SORT_STRING);
        
        return $paths;
    }
}
