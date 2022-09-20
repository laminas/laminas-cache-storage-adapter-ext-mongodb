<?php

declare(strict_types=1);

namespace LaminasTest\Cache\Storage\Adapter;

use Laminas\Cache\Exception;
use Laminas\Cache\Storage\Adapter\ExtMongoDbResourceManager;
use MongoDB\Client;
use MongoDB\Collection;
use PHPUnit\Framework\TestCase;
use stdClass;

use function getenv;

final class ExtMongoDbResourceManagerTest extends TestCase
{
    private ExtMongoDbResourceManager $object;

    private string $connectString;

    private string $database;

    private string $collectionName;

    protected function setUp(): void
    {
        $this->object         = new ExtMongoDbResourceManager();
        $this->connectString  = (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_CONNECTSTRING');
        $this->database       = (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_DATABASE');
        $this->collectionName = (string) getenv('TESTS_LAMINAS_CACHE_EXTMONGODB_COLLECTION');

        parent::setUp();
    }

    public function testSetResourceAlreadyCreated(): void
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));

        $client   = new Client($this->connectString);
        $resource = $client->selectCollection(
            $this->database,
            $this->collectionName
        );

        $this->object->setResource($id, $resource);

        $this->assertSame($resource, $this->object->getResource($id));
    }

    public function testSetResourceArray(): void
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));

        $server = 'mongodb://test:1234';

        $this->object->setResource($id, ['server' => $server]);

        $this->assertSame($server, $this->object->getServer($id));
    }

    public function testSetResourceThrowsException(): void
    {
        $id       = 'foo';
        $resource = new stdClass();

        $this->expectException(Exception\InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument */
        $this->object->setResource($id, $resource);
    }

    public function testHasResourceEmpty(): void
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));
    }

    public function testHasResourceSet(): void
    {
        $id = 'foo';

        $this->object->setResource($id, ['foo' => 'bar']);

        $this->assertTrue($this->object->hasResource($id));
    }

    public function testGetResourceNotSet(): void
    {
        $id = 'foo';

        $this->assertFalse($this->object->hasResource($id));

        $this->expectException(Exception\RuntimeException::class);
        $this->object->getResource($id);
    }

    public function testGetResourceInitialized(): void
    {
        $id = 'foo';

        $client   = new Client($this->connectString);
        $resource = $client->selectCollection(
            $this->database,
            $this->collectionName
        );

        $this->object->setResource($id, $resource);

        $this->assertSame($resource, $this->object->getResource($id));
    }

    public function testCorrectDatabaseResourceName(): void
    {
        $id = 'foo';

        $resource = [
            'db'     => $this->database,
            'server' => $this->connectString,
        ];

        $this->object->setResource($id, $resource);

        $this->assertSame($resource['db'], $this->object->getResource($id)->getDatabaseName());
    }

    public function testGetResourceNewResource(): void
    {
        $id                = 'foo';
        $server            = $this->connectString;
        $connectionOptions = ['connectTimeoutMS' => 5];
        $database          = $this->database;
        $collection        = $this->collectionName;

        $this->object->setServer($id, $server);
        $this->object->setConnectionOptions($id, $connectionOptions);
        $this->object->setDatabase($id, $database);
        $this->object->setCollection($id, $collection);

        $this->assertInstanceOf(Collection::class, $this->object->getResource($id));
    }

    public function testGetResourceUnknownServerThrowsException(): void
    {
        $id                = 'foo';
        $server            = 'mongodb://unknown.unknown';
        $connectionOptions = ['connectTimeoutMS' => 5];
        $database          = $this->database;
        $collection        = $this->collectionName;

        $this->object->setServer($id, $server);
        $this->object->setConnectionOptions($id, $connectionOptions);
        $this->object->setDatabase($id, $database);
        $this->object->setCollection($id, $collection);

        $this->expectException(Exception\RuntimeException::class);
        $this->object->getResource($id);
    }

    public function testGetSetCollection(): void
    {
        $resourceId     = 'testResource';
        $collectionName = 'testCollection';

        $this->object->setCollection($resourceId, $collectionName);
        $this->assertSame($collectionName, $this->object->getCollection($resourceId));
    }

    public function testGetSetConnectionOptions(): void
    {
        $resourceId        = 'testResource';
        $connectionOptions = ['test1' => 'option1', 'test2' => 'option2'];

        $this->object->setConnectionOptions($resourceId, $connectionOptions);
        $this->assertSame($connectionOptions, $this->object->getConnectionOptions($resourceId));
    }

    public function testGetSetServer(): void
    {
        $resourceId = 'testResource';
        $server     = 'testServer';

        $this->object->setServer($resourceId, $server);
        $this->assertSame($server, $this->object->getServer($resourceId));
    }

    public function testGetSetDriverOptions(): void
    {
        $resourceId    = 'testResource';
        $driverOptions = ['test1' => 'option1', 'test2' => 'option2'];

        $this->object->setDriverOptions($resourceId, $driverOptions);
        $this->assertSame($driverOptions, $this->object->getDriverOptions($resourceId));
    }

    public function testGetSetDatabase(): void
    {
        $resourceId = 'testResource';
        $database   = 'testDatabase';

        $this->object->setDatabase($resourceId, $database);
        $this->assertSame($database, $this->object->getDatabase($resourceId));
    }
}
