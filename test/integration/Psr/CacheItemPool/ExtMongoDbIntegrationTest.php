<?php

declare(strict_types=1);

namespace LaminasTest\Cache\Storage\Adapter\Psr\CacheItemPool;

use Laminas\Cache\Storage\Adapter\ExtMongoDb;
use Laminas\Cache\Storage\Adapter\ExtMongoDbOptions;
use Laminas\Cache\Storage\FlushableInterface;
use Laminas\Cache\Storage\Plugin\Serializer;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Serializer\AdapterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use LaminasTest\Cache\Storage\Adapter\AbstractCacheItemPoolIntegrationTest;

use function getenv;
use function sprintf;

/**
 * @uses FlushableInterface
 *
 * @template-extends AbstractCacheItemPoolIntegrationTest<ExtMongoDbOptions>
 */
final class ExtMongoDbIntegrationTest extends AbstractCacheItemPoolIntegrationTest
{
    private const LONG_KEY_SUPPORT_POSTPONED = 'Long key support will be provided with a dedicated ticket.';

    protected function setUp(): void
    {
        parent::setUp();
        $deferredSkippedMessage                                                 = sprintf(
            '%s storage doesn\'t support driver deferred',
            ExtMongoDb::class
        );
        $this->skippedTests['testHasItemReturnsFalseWhenDeferredItemIsExpired'] = $deferredSkippedMessage;
        $this->skippedTests['testBinaryData']                                   = 'Binary data not supported';
        $this->skippedTests['testBasicUsageWithLongKey']                        = self::LONG_KEY_SUPPORT_POSTPONED;
    }

    protected function createStorage(): StorageInterface&FlushableInterface
    {
        $storage = new ExtMongoDb([
            'server'     => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);

        $storage->addPlugin(new Serializer(new AdapterPluginManager(new ServiceManager())));
        return $storage;
    }
}
