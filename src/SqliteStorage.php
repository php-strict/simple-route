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
 * Routes storage based on standalone SQLite database.
 */
class SqliteStorage extends AbstractStorage
{
    /**
     * @var \SQLite3
     */
    protected $db;
    
    /**
     * @var string
     */
    protected $base = '/routes/routes.db';
    
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
     * @param string $base = null
     * @param string $table = null
     * @param string $keyField = null
     * @param string $dataField = null
     */
    public function __construct(
        string $base = null, 
        string $table = null, 
        string $keyField = null, 
        string $dataField = null
    ) {
        if (isset($base)) {
            $this->base = $base;
        }
        if (isset($table)) {
            $this->table = $table;
        }
        if (isset($keyField)) {
            $this->keyField = $keyField;
        }
        if (isset($dataField)) {
            $this->dataField = $dataField;
        }
        
        try {
            $this->db = new \SQLite3($this->base);
        } catch (\Throwable $e) {
            throw new StorageConnectException($e->getMessage());
        }
    }
    
    public function __destruct()
    {
        if (null !== $this->db) {
            $this->db->close();
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
        $sql =  'SELECT "' . $this->dataField . '"'
                . ' FROM "' . $this->table . '"'
                . ' WHERE "' . $this->keyField . '"'
                . "='" . $this->db->escapeString($key) . "'";
        
        return new StorageEntry($key, $this->getDataByQuery($sql));
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
        $sql =  'SELECT "' . $this->dataField . '"'
                . ' FROM "' . $this->table . '"'
                . ' WHERE "' . $this->keyField . '"'
                . " IN('" . implode("','", $this->getPaths($key)) . "')"
                . ' ORDER BY "' . $this->keyField . '" DESC'
                . ' LIMIT 1';
        
        return new StorageEntry($key, $this->getDataByQuery($sql));
    }
    
    /**
     * Gets storages entry by SQL query.
     * 
     * @param string $query
     * 
     * @return array
     * 
     * @throws \PhpStrict\SimpleRoute\NotFoundException
     * @throws \PhpStrict\SimpleRoute\BadStorageEntryException
     */
    protected function getDataByQuery(string $query): array
    {
        $row = $this->db->querySingle($query, true);
        if (empty($row)) {
            throw new NotFoundException();
        }
        
        if (!is_array($row) || !array_key_exists($this->dataField, $row)) {
            throw new BadStorageEntryException(); //@codeCoverageIgnore
        }
        
        $data = json_decode($row[$this->dataField], true);
        if (!is_array($data)) {
            throw new BadStorageEntryException();
        }
        
        return $data;
    }
}
