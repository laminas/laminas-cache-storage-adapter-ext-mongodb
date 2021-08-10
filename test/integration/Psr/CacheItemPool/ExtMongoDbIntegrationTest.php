<?php

namespace LaminasTest\Cache\Psr\CacheItemPool;

use Cache\IntegrationTests\CachePoolTest;
use Laminas\Cache\Psr\CacheItemPool\CacheItemPoolDecorator;
use Laminas\Cache\Storage\Plugin\Serializer;
use Laminas\Cache\StorageFactory;
use Psr\Cache\CacheItemPoolInterface;

use function date_default_timezone_get;
use function date_default_timezone_set;
use function get_class;
use function getenv;
use function sprintf;

class ExtMongoDbIntegrationTest extends CachePoolTest
{
    /**
     * Backup default timezone
     *
     * @var string
     */
    private $tz;

    protected function setUp(): void
    {
        // set non-UTC timezone
        $this->tz = date_default_timezone_get();
        date_default_timezone_set('America/Vancouver');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->tz);

        parent::tearDown();
    }

    public function createCachePool(): CacheItemPoolInterface
    {
        $storage = StorageFactory::adapterFactory('extmongodb', [
            'server'     => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);
        $storage->addPlugin(new Serializer());

        $deferredSkippedMessage                                                 = sprintf(
            '%s storage doesn\'t support driver deferred',
            get_class($storage)
        );
        $this->skippedTests['testHasItemReturnsFalseWhenDeferredItemIsExpired'] = $deferredSkippedMessage;
        $this->skippedTests['testBinaryData']                                   = 'Binary data not supported';

        return new CacheItemPoolDecorator($storage);
    }
}
