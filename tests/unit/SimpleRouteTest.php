<?php
use PhpStrict\SimpleRoute\Route;
use PhpStrict\SimpleRoute\ArrayStorage;
use PhpStrict\SimpleRoute\SqliteStorage;
use PhpStrict\SimpleRoute\StorageEntry;
use PhpStrict\SimpleRoute\StorageInterface;
 
class SimpleRouteTest extends \Codeception\Test\Unit
{
    /**
     * @param string $expectedExceptionClass
     * @param callable $call = null
     */
    protected function expectedException(string $expectedExceptionClass, callable $call = null): void
    {
        try {
            $call();
        } catch (\Exception $e) {
            $this->assertEquals($expectedExceptionClass, get_class($e));
            return;
        }
        if ('' != $expectedExceptionClass) {
            $this->fail('Expected exception not throwed');
        }
    }
    
    protected function getRoutes(): array
    {
        return [
            '/' => [
                'title'     => 'root title',
                'callback'  => function () {
                    return 'root callback result';
                },
            ],
            '/qwe' => [],
            '/qwe/rty' => [],
            '/qwe/rty/uio' => [],
            '/bad-entry-path' => 'bad entry',
        ];
    }
    
    protected function testStorageEmpty(StorageInterface $storage): void
    {
        $this->expectedException(
            PhpStrict\SimpleRoute\NotFoundException::class,
            function () use ($storage) {
                $storage->get('');
            }
        );
        $this->expectedException(
            PhpStrict\SimpleRoute\NotFoundException::class,
            function () use ($storage) {
                $storage->find('');
            }
        );
    }
    
    public function testArrayStorageEmpty()
    {
        $this->testStorageEmpty(new ArrayStorage([]));
	}
	
	public function testArrayStorageFilled()
	{
        $routes = $this->getRoutes();
        $storage = new ArrayStorage($routes);
        
        $entry = $storage->get('/');
        $this->assertNotNull($entry);
        $this->assertInstanceOf(StorageEntry::class, $entry);
        $this->assertEquals('/', $entry->key);
        $this->assertTrue(is_array($entry->data));
        $this->assertCount(2, $entry->data);
        $this->assertEquals('root title', $entry->data['title']);
        $this->assertTrue(is_callable($entry->data['callback']));
        $this->assertEquals('root callback result', $entry->data['callback']());
        
        $entry = $storage->find('/qwe/rty/param1/param2');
        $this->assertNotNull($entry);
        $this->assertInstanceOf(StorageEntry::class, $entry);
        $this->assertEquals('/qwe/rty', $entry->key);
        
        $entry = $storage->find('/qwe/param0/param1/param2');
        $this->assertNotNull($entry);
        $this->assertInstanceOf(StorageEntry::class, $entry);
        $this->assertEquals('/qwe', $entry->key);
        
		$this->expectedException(
            PhpStrict\SimpleRoute\NotFoundException::class,
            function () use ($storage) {
                $storage->get('/non-existence-path');
            }
        );
        
        $this->expectedException(
            PhpStrict\SimpleRoute\BadStorageEntryException::class,
            function () use ($storage) {
                $storage->get('/bad-entry-path');
            }
        );
    }
    
    protected function getSqliteStorage(): SqliteStorage
    {
        $storage = new class('') extends SqliteStorage {
            public $db;
        };
        
        $storage->db->exec('CREATE TABLE routes ("key" VARCHAR(255) PRIMARY KEY, "data" text)');
        
        return $storage;
    }
    
    public function testSqliteStorageEmpty()
    {
        $storage = $this->getSqliteStorage();
        
        $this->testStorageEmpty($storage);
        
        unset($storage);
    }
    
    public function testSqliteStorageFilled()
    {
        $storage = $this->getSqliteStorage();
        
        $sql = '';
        foreach ($this->getRoutes() as $key => $data) {
            $sql .= ",('" . $key . "', '" . json_encode($data) . "')";
        }
        $sql =  'INSERT INTO routes ("key", "data")'
                . ' VALUES'
                . substr($sql, 1);
        $storage->db->exec($sql);
        
        $entry = $storage->get('/');
        $this->assertNotNull($entry);
        $this->assertInstanceOf(StorageEntry::class, $entry);
        $this->assertEquals('/', $entry->key);
        $this->assertTrue(is_array($entry->data));
        $this->assertCount(2, $entry->data);
        $this->assertEquals('root title', $entry->data['title']);
        $this->assertFalse(is_callable($entry->data['callback']));
        
        $entry = $storage->find('/qwe/rty/param1/param2');
        $this->assertNotNull($entry);
        $this->assertInstanceOf(StorageEntry::class, $entry);
        $this->assertEquals('/qwe/rty', $entry->key);
        
        $entry = $storage->find('/qwe/param0/param1/param2');
        $this->assertNotNull($entry);
        $this->assertInstanceOf(StorageEntry::class, $entry);
        $this->assertEquals('/qwe', $entry->key);
        
        unset($storage);
    }
}
