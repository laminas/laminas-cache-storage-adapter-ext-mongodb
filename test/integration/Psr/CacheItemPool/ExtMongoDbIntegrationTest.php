<?php

namespace LaminasTest\Cache\Storage\Adapter\Psr\CacheItemPool;

use Laminas\Cache\Storage\Adapter\ExtMongoDb;
use Laminas\Cache\Storage\Plugin\Serializer;
use Laminas\Cache\Storage\PluginAwareInterface;
use Laminas\Cache\Storage\StorageInterface;
use LaminasTest\Cache\Storage\Adapter\AbstractCacheItemPoolIntegrationTest;

use function getenv;
use function sprintf;

class ExtMongoDbIntegrationTest extends AbstractCacheItemPoolIntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $deferredSkippedMessage = sprintf(
            '%s storage doesn\'t support driver deferred',
            ExtMongoDb::class
        );
        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testHasItemReturnsFalseWhenDeferredItemIsExpired'] = $deferredSkippedMessage;
        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testBinaryData'] = 'Binary data not supported';
    }

    protected function createStorage(): StorageInterface
    {
        $storage = new ExtMongoDb([
            'server'     => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);

        self::assertInstanceOf(PluginAwareInterface::class, $storage);
        $storage->addPlugin(new Serializer());
        return $storage;
    }
}
