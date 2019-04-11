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
 * Routes storage based on provided array.
 */
class ArrayStorage extends AbstractStorage
{
    /**
     * @var array
     */
    protected $storage = [];
    
    /**
     * @param array $storage
     */
    public function __construct(array $storage)
    {
        $this->storage = $storage;
        
        if (0 == count($this->storage)) {
            return;
        }
        
        krsort($this->storage, SORT_STRING);
    }
    
    /**
     * Gets storage entry by key.
     * 
     * @param string $key
     * 
     * @return \PhpStrict\SimpleRoute\StorageEntry
     * 
     * @throws \PhpStrict\SimpleRoute\NotFoundException
     * @throws \PhpStrict\SimpleRoute\BadStorageEntryException
     */
    public function get(string $key): StorageEntry
    {
        if (!array_key_exists($key, $this->storage)) {
            throw new NotFoundException();
        }
        
        return new StorageEntry($key, $this->getEntry($key));
    }
    
    /**
     * Looking for entry closest to key.
     * 
     * @param string $key
     * 
     * @return \PhpStrict\SimpleRoute\StorageEntry
     * 
     * @throws \PhpStrict\SimpleRoute\NotFoundException
     * @throws \PhpStrict\SimpleRoute\BadStorageEntryException
     */
    public function find(string $key): StorageEntry
    {
        $paths = $this->getPaths($key);
        
        foreach ($paths as $path) {
            if (!array_key_exists($path, $this->storage)) {
                continue;
            }
            
            return new StorageEntry($path, $this->getEntry($path));
        }
        
        throw new NotFoundException();
    }
    
    /**
     * Gets route storage entry.
     * 
     * @param string $key
     * 
     * @return array
     * 
     * @throws \PhpStrict\SimpleRoute\BadStorageEntryException
     */
    protected function getEntry(string $key): array
    {
        $data = $this->storage[$key];
        if (is_array($data)) {
            return $data;
        }
        
        throw new BadStorageEntryException();
    }
}
