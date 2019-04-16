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
 * Routes storage based on project MySQL database.
 */
class MysqlStorage extends AbstractStorage
{
    /**
     * @var \mysqli
     */
    protected $db;
    
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
     * Requires link to database connected object provided by mysqli extension.
     * 
     * @param \mysqli $db
     * @param ?string $table = null
     * @param ?string $keyField = null
     * @param ?string $dataField = null
     */
    public function __construct(
        \mysqli $db, 
        string $table = null, 
        string $keyField = null, 
        string $dataField = null
    ) {
        $this->db = $db;
        
        if (isset($table)) {
            $this->table = $table;
        }
        if (isset($keyField)) {
            $this->keyField = $keyField;
        }
        if (isset($dataField)) {
            $this->dataField = $dataField;
        }
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
        $sql =  'SELECT `' . $this->keyField . '`,`' . $this->dataField . '`'
                . ' FROM `' . $this->table . '`'
                . ' WHERE `' . $this->keyField . '`'
                . "='" . $this->db->real_escape_string($key) . "'";
        
        [$key, $data] = $this->getEntryByQuery($sql);
        return new StorageEntry($key, $data);
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
        $sql =  'SELECT `' . $this->keyField . '`,`' . $this->dataField . '`'
                . ' FROM `' . $this->table . '`'
                . ' WHERE `' . $this->keyField . '`'
                . " IN('" . implode("','", $this->getPaths($key)) . "')"
                . ' ORDER BY `' . $this->keyField . '` DESC'
                . ' LIMIT 1';
        
        [$key, $data] = $this->getEntryByQuery($sql);
        return new StorageEntry($key, $data);
    }
    
    /**
     * Gets storages entry by SQL query.
     * 
     * @param string $query
     * 
     * @return array [string $key, array $data]
     * 
     * @throws \PhpStrict\SimpleRoute\NotFoundException
     * @throws \PhpStrict\SimpleRoute\BadStorageEntryException
     */
    protected function getEntryByQuery(string $query): array
    {
        $result = $this->db->query($query);
        if (!$result) {
            throw new StorageException('Query failed');
        }
        
        $row = $result->fetch_assoc();
        $result->free();
        
        if (!is_array($row) || !array_key_exists($this->dataField, $row)) {
            throw new NotFoundException();
        }
        
        $data = json_decode($row[$this->dataField], false);
        if (!is_object($data) && !is_array($data)) {
            throw new BadStorageEntryException();
        }
        
        return [$row[$this->keyField], (array) $data];
    }
}
