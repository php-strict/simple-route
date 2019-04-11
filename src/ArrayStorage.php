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
    public function get(string $path): StorageEntry
    {
        if (!array_key_exists($path, $this->storage)) {
            throw new NotFoundException();
        }
        
        return new StorageEntry($path, $this->getEntry($path));
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
    public function find(string $path): StorageEntry
    {
        $paths = $this->getPaths($path);
        
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
     * @param string $path
     * 
     * @return array
     * 
     * @throws \PhpStrict\SimpleRoute\BadStorageEntryException
     */
    protected function getEntry(string $path): array
    {
        $data = $this->storage[$path];
        if (is_array($data)) {
            return $data;
        }
        
        throw new BadStorageEntryException();
    }
}
