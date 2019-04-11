<?php
use PhpStrict\SimpleRoute\Route;
use PhpStrict\SimpleRoute\ArrayStorage;
use PhpStrict\SimpleRoute\StorageEntry;
 
class SimpleRouteTest extends \Codeception\Test\Unit
{
    /**
     * @param string $expectedExceptionClass
     * @param callable $call = null
     */
    protected function expectedException(string $expectedExceptionClass, callable $call = null)
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
    
    public function testArrayStorage()
    {
        $data = [
            '/' => [
                'title'     => 'root title',
                'callback'  => function () {
                    return 'root callback result';
                },
            ],
            '/bad-entry-path' => 'bad entry',
        ];
        $storage = new ArrayStorage($data);
        
        $entry = $storage->get('/');
        $this->assertNotNull($entry);
        $this->assertInstanceOf(StorageEntry::class, $entry);
        $this->assertEquals('/', $entry->key);
        $this->assertTrue(is_array($entry->data));
        $this->assertCount(2, $entry->data);
        $this->assertEquals('root title', $entry->data['title']);
        $this->assertTrue(is_callable($entry->data['callback']));
        $this->assertEquals('root callback result', $entry->data['callback']());
        
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
}
