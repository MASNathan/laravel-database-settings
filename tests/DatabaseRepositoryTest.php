<?php

namespace MASNathan\LaravelDatabaseSettings\Tests;


use Illuminate\Contracts\Config\Repository;
use MASNathan\LaravelDatabaseSettings\DatabaseRepository;
use MASNathan\LaravelDatabaseSettings\RepositoryManager;

class DatabaseRepositoryTest extends DatabaseTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app['config.database'];
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Repository::class, $this->repository);
    }

    public function testAllEmpty()
    {
        $this->assertEmpty($this->repository->all());
    }

    public function testAll()
    {
        $values = [
            'key1' => 'value1',
            'key2' => [
                'subkey1' => 'subvalue1',
                'subkey2' => 'subvalue2',
            ],
        ];
        $this->repository->set('key1', $values['key1']);
        $this->repository->set('key2', $values['key2']);
        $this->assertNotEmpty($this->repository->all());

        $this->assertSame('value1', $this->repository->get('key1'));
        $this->assertSame('subvalue1', $this->repository->get('key2.subkey1'));
        $this->assertSame('subvalue2', $this->repository->get('key2.subkey2'));
    }

    public function testGetWithDefault()
    {
        $this->assertEquals('default', $this->repository->get('not-exist', 'default'));
    }

    public function testGetWithNull()
    {
        $this->assertNull($this->repository->get('not-exist'));
    }

    public function testSetAndGet()
    {
        $this->assertNull($this->repository->set('app', ['random' => ['key' => 'value']]));
        $this->assertEquals('value', $this->repository->get('app.random.key'));

        $repository = new DatabaseRepository();
        $appConfig = $repository->get('app');
        $this->assertArrayHasKey('random', $appConfig);
        $this->assertEquals('value', $appConfig['random']['key']);
    }

    public function testHasIsTrue()
    {
        $this->repository->set('app.random.key', 'value');
        $this->assertTrue($this->repository->has('app.random.key'));
    }

    public function testHasIsTFalse()
    {
        $this->assertFalse($this->repository->has('not-exist'));
    }

    public function testSetArray()
    {
        $this->repository->set([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        $this->assertSame('value1', $this->repository->get('key1'));
        $this->assertSame('value2', $this->repository->get('key2'));
    }
}
