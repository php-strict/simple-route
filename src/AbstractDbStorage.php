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
 * Routes storage based on relational database.
 */
abstract class AbstractDbStorage extends AbstractStorage
{
    /**
     * @var string
     */
    protected $table = 'routes';
    
    /**
     * @var string
     */
    protected $keyField = 'key';
    
    /**
     * @var string
     */
    protected $dataField = 'data';
    
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
        $sql =  'SELECT ' . $this->getFields()
                . ' FROM ' . $this->getEscapedEntity($this->table)
                . ' WHERE ' . $this->getEscapedEntity($this->keyField)
                . "='" . $this->getEscapedString($key) . "'";
        
        return $this->getStorageEntry($sql);
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
        $sql =  'SELECT ' . $this->getFields()
                . ' FROM ' . $this->getEscapedEntity($this->table)
                . ' WHERE ' . $this->getEscapedEntity($this->keyField)
                . " IN('" . implode("','", $this->getPaths($key)) . "')"
                . ' ORDER BY ' . $this->getEscapedEntity($this->keyField) . ' DESC'
                . ' LIMIT 1';
        
        return $this->getStorageEntry($sql);
    }
    
    /**
     * Return set of table fields.
     * 
     * @return string
     */
    protected function getFields(): string
    {
        return '*' == $this->dataField
                ? '*'
                : $this->getEscapedEntity($this->keyField) . ','
                . $this->getEscapedEntity($this->dataField);
    }
    
    /**
     * Return escaped (database dependent) entity (field, table name).
     * 
     * @param string $str
     * 
     * @return string
     */
    protected function getEscapedEntity(string $str): string
    {
        return $str;
    }
    
    /**
     * Return escaped (database dependent) string.
     * 
     * @param string $str
     * 
     * @return string
     */
    protected function getEscapedString(string $str): string
    {
        return addslashes($str);
    }
    
    /**
     * Gets StorageEntry object by SQL query.
     * 
     * @param string $query
     * 
     * @return \PhpStrict\SimpleRoute\StorageEntry
     */
    protected function getStorageEntry(string $query): StorageEntry
    {
        [$key, $data] = $this->getKeyEntryByQuery($query);
        return new StorageEntry($key, $data);
    }
    
    /**
     * Gets pair [key, entry] from storage by SQL query.
     * 
     * @param string $query
     * 
     * @return array [string $key, array $data]
     * 
     * @throws \PhpStrict\SimpleRoute\NotFoundException
     * @throws \PhpStrict\SimpleRoute\BadStorageEntryException
     */
    abstract protected function getKeyEntryByQuery(string $query): array;
}
