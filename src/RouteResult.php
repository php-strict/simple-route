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
 * Routing result. 
 * 
 * Contains route storage entry and rest of the path as parameters array.
 */
class RouteResult
{
    /**
     * @var \PhpStrict\SimpleRoute\StorageEntry
     */
    public $entry;
    
    /**
     * @var array
     */
    public $params = [];
    
    /**
     * @param \PhpStrict\SimpleRoute\StorageEntry $entry
     * @param array $params = []
     */
    public function __construct(StorageEntry $entry, array $params = [])
    {
        $this->entry = $entry;
        $this->params = $params;
    }
}
