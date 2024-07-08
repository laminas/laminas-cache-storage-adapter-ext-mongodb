<?php

declare(strict_types=1);

namespace Laminas\Cache\Storage\Adapter;

use Laminas\Cache\Exception;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\Exception as MongoDriverException;

use function assert;
use function is_array;
use function is_string;

final class ExtMongoDbResourceManager implements ExtMongoDbResourceManagerInterface
{
    /**
     * Registered resources
     *
     * @var array[]
     */
    private array $resources = [];

    public function hasResource(string $id): bool
    {
        return isset($this->resources[$id]);
    }

    public function setResource(string $id, array|Collection $resource): void
    {
        if ($resource instanceof Collection) {
            $this->resources[$id] = [
                'db'                  => $resource->getDatabaseName(),
                'collection'          => (string) $resource,
                'collection_instance' => $resource,
            ];

            return;
        }

        $this->resources[$id] = $resource;
    }

    public function getResource(string $id): Collection
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $resource = $this->resources[$id];
        if (! isset($resource['collection_instance'])) {
            try {
                /** @psalm-suppress MixedAssignment */
                $clientInstance = $resource['client_instance'] ?? null;
                if (! $clientInstance instanceof Client) {
                    $clientInstance = new Client(
                        (string) ($resource['server'] ?? ''),
                        (array) ($resource['connection_options'] ?? []),
                        (array) ($resource['driver_options'] ?? [])
                    );
                }

                $resource['client_instance'] = $clientInstance;

                $collection = $clientInstance->selectCollection(
                    (string) ($resource['db'] ?? 'laminas'),
                    (string) ($resource['collection'] ?? 'cache')
                );
                $collection->createIndex(['key' => 1]);

                $this->resources[$id]['collection_instance'] = $collection;
            } catch (MongoDriverException $e) {
                throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $instance = $this->resources[$id]['collection_instance'];
        assert($instance instanceof Collection);

        return $instance;
    }

    public function setServer(string $id, string $server): void
    {
        $this->resources[$id]['server'] = $server;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    public function getServer(string $id): null|string
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $server = $this->resources[$id]['server'] ?? null;
        if (! is_string($server)) {
            return null;
        }

        return $server;
    }

    public function setConnectionOptions(string $id, array $connectionOptions): void
    {
        $this->resources[$id]['connection_options'] = $connectionOptions;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    public function getConnectionOptions(string $id): array
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $options = $this->resources[$id]['connection_options'] ?? [];
        if (! is_array($options)) {
            return [];
        }

        return $options;
    }

    public function setDriverOptions(string $id, array $driverOptions): void
    {
        $this->resources[$id]['driver_options'] = $driverOptions;

        unset($this->resources[$id]['client_instance']);
        unset($this->resources[$id]['collection_instance']);
    }

    public function getDriverOptions(string $id): array
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $options = $this->resources[$id]['driver_options'] ?? [];
        if (! is_array($options)) {
            return [];
        }

        return $options;
    }

    public function setDatabase(string $id, string $database): void
    {
        $this->resources[$id]['db'] = $database;

        unset($this->resources[$id]['collection_instance']);
    }

    public function getDatabase(string $id): string
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $db = $this->resources[$id]['db'] ?? '';
        if (! is_string($db)) {
            return '';
        }

        return $db;
    }

    public function setCollection(string $id, string $collection): void
    {
        $this->resources[$id]['collection'] = $collection;

        unset($this->resources[$id]['collection_instance']);
    }

    public function getCollection(string $id): string
    {
        if (! $this->hasResource($id)) {
            throw new Exception\RuntimeException("No resource with id '{$id}'");
        }

        $collection = $this->resources[$id]['collection'] ?? '';
        if (! is_string($collection)) {
            return '';
        }

        return $collection;
    }
}
