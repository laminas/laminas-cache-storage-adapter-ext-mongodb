<?php

declare(strict_types=1);

namespace LaminasTest\Cache\Storage\Adapter;

use Laminas\Cache\Storage\Adapter\ExtMongoDb;
use Laminas\Cache\Storage\Adapter\ExtMongoDbOptions;
use Laminas\Cache\Storage\FlushableInterface;

use function getenv;

/** @template-extends AbstractCommonAdapterTest<ExtMongoDb,ExtMongoDbOptions> */
final class ExtMongoDbTest extends AbstractCommonAdapterTest
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
        if ($this->storage instanceof FlushableInterface) {
            $this->storage->flush();
        }

        parent::tearDown();
    }

    public function testSetOptionsNotMongoDbOptions(): void
    {
        $this->storage->setOptions([
            'server'     => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);

        $this->assertInstanceOf(ExtMongoDbOptions::class, $this->storage->getOptions());
    }
}
