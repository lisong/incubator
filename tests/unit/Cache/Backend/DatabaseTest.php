<?php

namespace Phalcon\Test\Cache\Backend;

use Phalcon\Cache\Backend\Database as CacheBackend;
use Phalcon\Cache\Frontend\Data as CacheFrontend;
use Phalcon\Db\Adapter\Pdo\Sqlite as DbAdapter;
use Codeception\TestCase\Test;
use UnitTester;

/**
 * \Phalcon\Test\Cache\Backend\DatabaseTest
 * Tests for Phalcon\Cache\Backend\Database component
 *
 * @copyright (c) 2011-2015 Phalcon Team
 * @link      http://www.phalconphp.com
 * @author    Nikita Vershinin <endeveit@gmail.com>
 * @package   Phalcon\Test\Cache\Backend
 * @group     Cache
 *
 * The contents of this file are subject to the New BSD License that is
 * bundled with this package in the file docs/LICENSE.txt
 *
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world-wide-web, please send an email to license@phalconphp.com
 * so that we can send you a copy immediately.
 */
class DatabaseTest extends Test
{
    /**
     * UnitTester Object
     * @var UnitTester
     */
    protected $tester;

    protected $key = 'DB_key';
    protected $data = 'DB_data';

    /**
     * executed before each test
     */
    protected function _before()
    {
    }

    /**
     * executed after each test
     */
    protected function _after()
    {
    }

    public function testPrefixed()
    {
        $backend = $this->getBackend('pre_');

        $this->runTests($backend);
        $this->runTests($backend, 1);
    }

    public function testNotPrefixed()
    {
        $backend = $this->getBackend();

        $this->runTests($backend);
        $this->runTests($backend, 1);
    }

    protected function getBackend($prefix = '')
    {
        $frontend   = new CacheFrontend(['lifetime' => 10]);
        $connection = new DbAdapter(['dbname' => ':memory:']);

        // Make table structure
        $connection->getInternalHandler()->exec(
            'CREATE TABLE "cache_data" ("key_name" TEXT PRIMARY KEY, "data" TEXT, "lifetime" INTEGER)'
        );

        return new CacheBackend($frontend, [
            'db'     => $connection,
            'table'  => 'cache_data',
            'prefix' => $prefix,
        ]);
    }

    protected function runTests(CacheBackend $backend, $lifetime = null)
    {
        $backend->save($this->key, $this->data, $lifetime);

        $this->assertTrue($backend->exists($this->key));
        $this->assertEquals($this->data, $backend->get($this->key));
        $this->assertNotEmpty($backend->queryKeys());
        $this->assertNotEmpty($backend->queryKeys('DB_'));
        $this->assertTrue($backend->delete($this->key));
        $this->assertFalse($backend->delete($this->key));

        if (null !== $lifetime) {
            $backend->save($this->key, $this->data, $lifetime);

            $this->assertTrue($backend->exists($this->key, $lifetime));
            $this->assertEquals($this->data, $backend->get($this->key, $lifetime));

            $backend->save($this->key, $this->data, -$lifetime);
            $this->assertFalse($backend->exists($this->key, -$lifetime));
        }
    }
}
