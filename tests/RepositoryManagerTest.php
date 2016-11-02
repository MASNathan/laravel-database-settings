<?php

namespace MASNathan\LaravelDatabaseSettings\Tests;


use Illuminate\Contracts\Config\Repository;
use MASNathan\LaravelDatabaseSettings\RepositoryManager;

class RepositoryManagerTest extends DatabaseTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->repository = $this->app['config'];
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Repository::class, $this->repository);
    }

    public function testGet()
    {
        $this->assertEquals($this->repository->get('app.name'), 'Laravel');
    }

    public function testGetWithDefault()
    {
        $this->assertEquals('default', $this->repository->get('not-exist', 'default'));
    }

    public function testGetWithNull()
    {
        $this->assertNull($this->repository->get('not-exist'));
    }

    public function testGetFile()
    {
        $this->assertEquals('web', $this->repository->get('auth.defaults.guard'));
    }

    public function testSet()
    {
        $this->assertNull($this->repository->set('app', ['random' => ['key' => 'value']]));
        $this->assertEquals('value', $this->repository->get('app.random.key'));

        $repository = new RepositoryManager();
        $appConfig = $repository->get('app');
        $this->assertArrayHasKey('random', $appConfig);
        $this->assertEquals('value', $appConfig['random']['key']);
    }

    public function testHasIsTrue()
    {
        $this->assertTrue($this->repository->has('app.name'));
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
