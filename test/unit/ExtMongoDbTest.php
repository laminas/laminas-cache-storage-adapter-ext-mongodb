<?php

/**
 * @see       https://github.com/laminas/laminas-cache for the canonical source repository
 * @copyright https://github.com/laminas/laminas-cache/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-cache/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Cache\Storage\Adapter;

use Laminas\Cache\Storage\Adapter\ExtMongoDb;
use Laminas\Cache\Storage\Adapter\ExtMongoDbOptions;
use MongoDB\Client;

/**
 * @covers Laminas\Cache\Storage\Adapter\ExtMongoDb<extended>
 */
class ExtMongoDbTest extends AbstractCommonAdapterTest
{
    public function setUp(): void
    {
        $this->options = new ExtMongoDbOptions([
            'server'     => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);

        $this->storage = new ExtMongoDb();
        $this->storage->setOptions($this->options);
        $this->storage->flush();

        parent::setUp();
    }

    public function tearDown(): void
    {
        if ($this->storage) {
            $this->storage->flush();
        }

        parent::tearDown();
    }

    public function getCommonAdapterNamesProvider()
    {
        return [
            ['ext_mongo_db'],
            ['extmongodb'],
            ['extMongoDb'],
            ['extMongoDB'],
            ['ExtMongoDb'],
            ['ExtMongoDB'],
        ];
    }

    public function testSetOptionsNotMongoDbOptions()
    {
        $this->storage->setOptions([
            'server'     => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);

        $this->assertInstanceOf(ExtMongoDbOptions::class, $this->storage->getOptions());
    }
}
