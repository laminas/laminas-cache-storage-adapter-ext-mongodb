<?php

namespace LaminasTest\Cache\Storage\Adapter;

use Laminas\Cache\Storage\Adapter\ExtMongoDbOptions;
use Laminas\Cache\Storage\Adapter\ExtMongoDbResourceManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers Laminas\Cache\Storage\Adapter\ExtMongoDbOptions<extended>
 */
class ExtMongoDbOptionsTest extends TestCase
{
    /** @var ExtMongoDbOptions */
    protected $object;

    public function setUp(): void
    {
        $this->object = new ExtMongoDbOptions();
    }

    public function testGetNamespaceSeparator()
    {
        $this->assertEquals(':', $this->object->getNamespaceSeparator());

        $namespaceSeparator = '_';

        $this->object->setNamespaceSeparator($namespaceSeparator);

        $this->assertEquals($namespaceSeparator, $this->object->getNamespaceSeparator());
    }

    public function testGetResourceManager()
    {
        $this->assertInstanceOf(
            ExtMongoDbResourceManager::class,
            $this->object->getResourceManager()
        );

        $resourceManager = new ExtMongoDbResourceManager();

        $this->object->setResourceManager($resourceManager);

        $this->assertSame($resourceManager, $this->object->getResourceManager());
    }

    public function testGetResourceId()
    {
        $this->assertEquals('default', $this->object->getResourceId());

        $resourceId = 'foo';

        $this->object->setResourceId($resourceId);

        $this->assertEquals($resourceId, $this->object->getResourceId());
    }

    public function testSetServer()
    {
        $resourceManager = new ExtMongoDbResourceManager();
        $this->object->setResourceManager($resourceManager);

        $resourceId = $this->object->getResourceId();
        $server     = 'mongodb://test:1234';

        $this->assertFalse($this->object->getResourceManager()->hasResource($resourceId));

        $this->object->setServer($server);
        $this->assertSame($server, $this->object->getResourceManager()->getServer($resourceId));
    }
}
