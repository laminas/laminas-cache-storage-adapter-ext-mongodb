<?php

declare(strict_types=1);

namespace Laminas\Cache\Storage\Adapter;

use Laminas\Cache\Storage\Adapter\ExtMongoDbResourceManager;

/**
 * Options for the ext-mongodb adapter implementation.
 *
 * If you are using ext-mongo, use the MongoDbOptions class instead.
 */
final class ExtMongoDbOptions extends AdapterOptions
{
    // @codingStandardsIgnoreStart
    /**
     * Prioritized properties ordered by prio to be set first
     * in case a bulk of options sets set at once
     *
     * @var string[]
     */
    protected array $__prioritizedProperties__ = [
        'resource_manager',
        'resource_id'
    ];
    // @codingStandardsIgnoreEnd
    /**
     * The namespace separator
     */
    private string $namespaceSeparator = ':';

    /**
     * The ext-mongodb resource manager
     */
    private ?ExtMongoDbResourceManagerInterface $resourceManager = null;

    /**
     * The resource id of the resource manager
     */
    private string $resourceId = 'default';

    public function setNamespaceSeparator(string $namespaceSeparator): self
    {
        if ($this->namespaceSeparator !== $namespaceSeparator) {
            $this->triggerOptionEvent('namespace_separator', $namespaceSeparator);

            $this->namespaceSeparator = $namespaceSeparator;
        }

        return $this;
    }

    public function getNamespaceSeparator(): string
    {
        return $this->namespaceSeparator;
    }

    public function setResourceManager(?ExtMongoDbResourceManagerInterface $resourceManager = null): self
    {
        if ($this->resourceManager !== $resourceManager) {
            $this->triggerOptionEvent('resource_manager', $resourceManager);

            $this->resourceManager = $resourceManager;
        }

        return $this;
    }

    public function getResourceManager(): ExtMongoDbResourceManagerInterface
    {
        return $this->resourceManager ??= new ExtMongoDbResourceManager();
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function setResourceId(string $resourceId): self
    {
        if ($this->resourceId !== $resourceId) {
            $this->triggerOptionEvent('resource_id', $resourceId);

            $this->resourceId = $resourceId;
        }

        return $this;
    }

    public function setServer(string $server): self
    {
        $this->getResourceManager()->setServer($this->getResourceId(), $server);
        return $this;
    }

    public function setConnectionOptions(array $connectionOptions): self
    {
        $this->getResourceManager()->setConnectionOptions($this->getResourceId(), $connectionOptions);
        return $this;
    }

    public function setDriverOptions(array $driverOptions): self
    {
        $this->getResourceManager()->setDriverOptions($this->getResourceId(), $driverOptions);
        return $this;
    }

    public function setDatabase(string $database): self
    {
        $this->getResourceManager()->setDatabase($this->getResourceId(), $database);
        return $this;
    }

    public function setCollection(string $collection): self
    {
        $this->getResourceManager()->setCollection($this->getResourceId(), $collection);
        return $this;
    }
}
