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
 * Route storage entry.
 */
class StorageEntry
{
    /**
     * @var string
     */
    public $key = '';
    
    /**
     * @var array
     */
    public $entry = [];
    
    /**
     * @param array $entry = []
     * @param array $params = []
     */
    public function __construct(string $key = '', array $entry = [])
    {
        $this->key = $key;
        $this->entry = $entry;
    }
}
