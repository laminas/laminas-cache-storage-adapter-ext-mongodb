<?php

declare(strict_types=1);

namespace Laminas\Cache\Storage\Adapter;

use Laminas\Cache\Exception;
use MongoDB\Collection;

/**
 * Resource manager for the ext-mongodb adapter.
 *
 * If you are using ext-mongo, use the MongoDbResourceManager instead.
 */
interface ExtMongoDbResourceManagerInterface
{
    /**
     * Check if a resource exists
     */
    public function hasResource(string $id): bool;

    /**
     * Set a resource
     *
     * @throws Exception\RuntimeException
     */
    public function setResource(string $id, array|Collection $resource): void;

    /**
     * Instantiate and return the Collection resource
     *
     * @throws Exception\RuntimeException
     */
    public function getResource(string $id): Collection;

    public function setServer(string $id, string $server): void;

    /**
     * @throws Exception\RuntimeException If no matching resource discovered.
     */
    public function getServer(string $id): null|string;

    public function setConnectionOptions(string $id, array $connectionOptions): void;

    /**
     * @throws Exception\RuntimeException If no matching resource discovered.
     */
    public function getConnectionOptions(string $id): array;

    public function setDriverOptions(string $id, array $driverOptions): void;

    /**
     * @throws Exception\RuntimeException If no matching resource discovered.
     */
    public function getDriverOptions(string $id): array;

    public function setDatabase(string $id, string $database): void;

    /**
     * @throws Exception\RuntimeException If no matching resource discovered.
     */
    public function getDatabase(string $id): string;

    public function setCollection(string $id, string $collection): void;

    /**
     * @throws Exception\RuntimeException If no matching resource discovered.
     */
    public function getCollection(string $id): string;
}
