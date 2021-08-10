<?php

namespace LaminasTest\Cache\Storage\Adapter;

use Laminas\Cache\Storage\Adapter\AdapterOptions;
use Laminas\Cache\Storage\Adapter\ExtMongoDbOptions;
use Laminas\Cache\Storage\Adapter\ExtMongoDbResourceManager;

/** @template-extends AbstractAdapterOptionsTest<ExtMongoDbOptions> */
final class ExtMongoDbOptionsTest extends AbstractAdapterOptionsTest
{
    protected function createAdapterOptions(): AdapterOptions
    {
        return new ExtMongoDbOptions();
    }

    /** @var ExtMongoDbOptions */
    protected $object;

    protected function setUp(): void
    {
        $this->object = $this->createAdapterOptions();
        parent::setUp();
    }

    public function testGetNamespaceSeparator(): void
    {
        $this->assertEquals(':', $this->object->getNamespaceSeparator());

        $namespaceSeparator = '_';

        $this->object->setNamespaceSeparator($namespaceSeparator);

        $this->assertEquals($namespaceSeparator, $this->object->getNamespaceSeparator());
    }

    public function testGetResourceManager(): void
    {
        $this->assertInstanceOf(
            ExtMongoDbResourceManager::class,
            $this->object->getResourceManager()
        );

        $resourceManager = new ExtMongoDbResourceManager();

        $this->object->setResourceManager($resourceManager);

        $this->assertSame($resourceManager, $this->object->getResourceManager());
    }

    public function testGetResourceId(): void
    {
        $this->assertEquals('default', $this->object->getResourceId());

        $resourceId = 'foo';

        $this->object->setResourceId($resourceId);

        $this->assertEquals($resourceId, $this->object->getResourceId());
    }

    public function testSetServer(): void
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
