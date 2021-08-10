<?php

namespace LaminasTest\Cache\Psr\CacheItemPool;

use Cache\IntegrationTests\CachePoolTest;
use Laminas\Cache\Psr\CacheItemPool\CacheItemPoolDecorator;
use Laminas\Cache\Storage\Adapter\ExtMongoDb;
use Laminas\Cache\Storage\Plugin\Serializer;
use Laminas\Cache\Storage\PluginAwareInterface;
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
        $storage = new ExtMongoDb([
            'server'     => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING'),
            'database'   => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE'),
            'collection' => (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION'),
        ]);

        self::assertInstanceOf(PluginAwareInterface::class, $storage);
        $storage->addPlugin(new Serializer());

        $deferredSkippedMessage = sprintf(
            '%s storage doesn\'t support driver deferred',
            get_class($storage)
        );
        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testHasItemReturnsFalseWhenDeferredItemIsExpired'] = $deferredSkippedMessage;
        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testBinaryData'] = 'Binary data not supported';

        return new CacheItemPoolDecorator($storage);
    }
}
