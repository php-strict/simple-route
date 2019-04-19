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
 * Routes storage based on text file.
 */
class FileStorage extends ArrayStorage
{
    /**
     * @param string $storagePath
     */
    public function __construct(string $storagePath)
    {
        if (!file_exists($storagePath)) {
            throw new StorageConnectException('Storage file not exists');
        }
        
        $storage = @include $storagePath;
        
        if (!is_array($storage)) {
            throw new StorageConnectException('Storage file has not valid structure');
        }
        
        parent::__construct($storage);
    }
}
