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
class MysqlStorage extends AbstractDbStorage
{
    /**
     * @var \mysqli
     */
    protected $db;
    
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
        ?string $table = null, 
        ?string $keyField = null, 
        ?string $dataField = null
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
     * Return escaped (database dependent) entity (field, table name).
     * 
     * @param string $str
     * 
     * @return string
     */
    protected function getEscapedEntity(string $str): string
    {
        return '`' . $str . '`';
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
        return $this->db->real_escape_string($str);
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
    protected function getKeyEntryByQuery(string $query): array
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
