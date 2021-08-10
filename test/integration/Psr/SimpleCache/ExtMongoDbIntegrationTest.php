<?php

namespace LaminasTest\Cache\Storage\Adapter\Psr\SimpleCache;

use Laminas\Cache\Storage\Adapter\ExtMongoDb;
use Laminas\Cache\Storage\Plugin\Serializer;
use Laminas\Cache\Storage\PluginAwareInterface;
use Laminas\Cache\Storage\StorageInterface;
use LaminasTest\Cache\Storage\Adapter\AbstractSimpleCacheIntegrationTest;

use function getenv;

class ExtMongoDbIntegrationTest extends AbstractSimpleCacheIntegrationTest
{
    protected function setUp(): void
    {
        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testBasicUsageWithLongKey'] = 'SimpleCacheDecorator requires keys to be <= 64 chars';
        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testBinaryData'] = 'Binary data not supported';

        parent::setUp();
    }

    protected function createStorage(): StorageInterface
    {
        $storage = new ExtMongoDb([
            'server'     => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);

        $serializer = new Serializer();
        self::assertInstanceOf(PluginAwareInterface::class, $storage);
        $storage->addPlugin($serializer);

        return $storage;
    }
}
