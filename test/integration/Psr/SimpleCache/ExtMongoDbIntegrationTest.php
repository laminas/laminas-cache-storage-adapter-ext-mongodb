<?php

namespace LaminasTest\Cache\Psr\SimpleCache;

use Cache\IntegrationTests\SimpleCacheTest;
use Laminas\Cache\Psr\SimpleCache\SimpleCacheDecorator;
use Laminas\Cache\Storage\PluginAwareInterface;
use Laminas\Cache\StorageFactory;
use Psr\SimpleCache\CacheInterface;

use function date_default_timezone_get;
use function date_default_timezone_set;
use function getenv;

class ExtMongoDbIntegrationTest extends SimpleCacheTest
{
    /**
     * Backup default timezone
     *
     * @var string
     */
    private $tz;

    /** @var string */
    private $connectString;

    /** @var string */
    private $databaseName;

    /** @var string */
    private $collection;

    protected function setUp(): void
    {
        // set non-UTC timezone
        $this->tz = date_default_timezone_get();
        date_default_timezone_set('America/Vancouver');

        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testBasicUsageWithLongKey'] = 'SimpleCacheDecorator requires keys to be <= 64 chars';
        /** @psalm-suppress MixedArrayAssignment */
        $this->skippedTests['testBinaryData'] = 'Binary data not supported';

        $this->connectString = (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING');
        $this->databaseName  = (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE');
        $this->collection    = (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION');

        parent::setUp();
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->tz);

        parent::tearDown();
    }

    public function createSimpleCache(): CacheInterface
    {
        $storage    = StorageFactory::adapterFactory('extmongodb', [
            'server'     => $this->connectString,
            'database'   => $this->databaseName,
            'collection' => $this->collection,
        ]);
        $serializer = StorageFactory::pluginFactory('serializer');
        self::assertInstanceOf(PluginAwareInterface::class, $storage);
        $storage->addPlugin($serializer);

        return new SimpleCacheDecorator($storage);
    }
}
