<?php
use PhpStrict\SimpleRoute\Route;
use PhpStrict\SimpleRoute\RouteResult;
use PhpStrict\SimpleRoute\ArrayStorage;
use PhpStrict\SimpleRoute\FileStorage;
use PhpStrict\SimpleRoute\MysqlStorage;
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
    
    protected function testStorageFilled(StorageInterface $storage, bool $withCallbacks = false): void
    {
        $entry = $storage->get('/');
        $this->assertNotNull($entry);
        $this->assertInstanceOf(StorageEntry::class, $entry);
        $this->assertEquals('/', $entry->key);
        $this->assertTrue(is_array($entry->data));
        $this->assertCount(2, $entry->data);
        $this->assertEquals('root title', $entry->data['title']);
        if ($withCallbacks) {
            $this->assertTrue(is_callable($entry->data['callback']));
            $this->assertEquals('root callback result', $entry->data['callback']());
        } else {
            $this->assertFalse(is_callable($entry->data['callback']));
        }
        
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
    
    public function testArrayStorageEmpty()
    {
        $this->testStorageEmpty(new ArrayStorage([]));
	}
	
	public function testArrayStorageFilled()
	{
        $this->testStorageFilled(
            new ArrayStorage($this->getRoutes()),
            true
        );
    }
    
    /**
     * @group file
     */
    public function testFileStorageEmpty()
    {
		$this->expectedException(
            PhpStrict\SimpleRoute\StorageConnectException::class,
            function () {
                $storage = new FileStorage('/tmp/non-existence-dir/routes.txt');
            }
        );
        
        $file = dirname(__DIR__) . '/_data/routes.txt';
        file_put_contents($file, '');
		
        $this->expectedException(
            PhpStrict\SimpleRoute\StorageConnectException::class,
            function () use ($file) {
                $storage = new FileStorage($file);
            }
        );
        
        file_put_contents($file, '<?php return [];');
        
        $this->testStorageEmpty(new FileStorage($file));
        
        unlink($file);
	}
    
    protected function getSqliteStorage(): SqliteStorage
    {
        $storage = new class('', 'routes', 'key', 'data') extends SqliteStorage {
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
		$this->expectedException(
            PhpStrict\SimpleRoute\StorageConnectException::class,
            function () {
                $storage = new SqliteStorage('/tmp/non-existence-dir/routes.db');
            }
        );
        
        $storage = $this->getSqliteStorage();
        
        $sql = '';
        foreach ($this->getRoutes() as $key => $data) {
            $sql .= ",('" . $key . "', '" . json_encode($data) . "')";
        }
        $sql =  'INSERT INTO routes ("key", "data")'
                . ' VALUES'
                . substr($sql, 1);
        $storage->db->exec($sql);
        
        $this->testStorageFilled($storage, false);
        
        unset($storage);
    }
    
    protected function getMysqlObject(): \mysqli
    {
        $mysqli = new \mysqli('localhost', 'root', '');
        $mysqli->query('CREATE DATABASE IF NOT EXISTS simple_route_test');
        $mysqli->query('USE simple_route_test');
        $mysqli->query('CREATE TEMPORARY TABLE IF NOT EXISTS routes (`key` VARCHAR(255) PRIMARY KEY, `data` text)');
        return $mysqli;
    }
    
    /**
     * @group mysql
     */
    public function testMysqlStorageEmpty()
    {
        $storage = new MysqlStorage($this->getMysqlObject(), 'routes', 'key', 'data');
        $this->testStorageEmpty($storage);
    }
    
    /**
     * @group mysql
     */
    public function testMysqlStorageFilled()
    {
        $mysqli = $this->getMysqlObject();
        $storage = new MysqlStorage($mysqli);
        
        $sql = '';
        foreach ($this->getRoutes() as $key => $data) {
            $sql .= ",('" . $key . "', '" . json_encode($data) . "')";
        }
        $sql =  'INSERT INTO routes (`key`, `data`)'
                . ' VALUES'
                . substr($sql, 1);
        $mysqli->query($sql);
        
        $this->testStorageFilled($storage);

        $storage = new MysqlStorage($mysqli, 'bad-table');
		$this->expectedException(
            PhpStrict\SimpleRoute\StorageException::class,
            function () use ($storage) {
                $storage->get('');
            }
        );
    }
    
    /**
     * @group route 
     */
    public function testRoute()
    {
        $routes = $this->getRoutes();
        $storage = new ArrayStorage($routes);
        
        $this->assertNull(Route::find('/non-existence-path', $storage));
        
        $result = Route::find('/', $storage);
        $this->assertNotNull($result);
        $this->assertInstanceOf(RouteResult::class, $result);
        $this->assertTrue(is_array($result->params));
        $this->assertCount(0, $result->params);
        $this->assertInstanceOf(StorageEntry::class, $result->entry);
        $this->assertEquals('/', $result->entry->key);
        $this->assertEquals($routes['/'], $result->entry->data);
        
        $this->assertEquals($result, Route::find('', $storage));
        
        $result = Route::find('/qwe/param1/param2', $storage);
        $this->assertTrue(is_array($result->params));
        $this->assertCount(2, $result->params);
        $this->assertEquals(['param1', 'param2'], $result->params);
    }
}
