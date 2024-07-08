<?php

declare(strict_types=1);

namespace Laminas\Cache\Storage\Adapter;

use ArrayObject;
use Laminas\Cache\Exception;
use Laminas\Cache\Storage\AbstractMetadataCapableAdapter;
use Laminas\Cache\Storage\Adapter\ExtMongoDb\Metadata;
use Laminas\Cache\Storage\Capabilities;
use Laminas\Cache\Storage\FlushableInterface;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\BSON\UTCDateTime as MongoDate;
use MongoDB\Collection;
use MongoDB\Driver\Exception\Exception as MongoDriverException;

use function array_key_exists;
use function array_map;
use function assert;
use function get_debug_type;
use function is_array;
use function is_iterable;
use function microtime;
use function round;
use function sprintf;

/**
 * Cache storage adapter for ext-mongodb
 *
 * If you are using ext-mongo, use the MongoDb adapter instead.
 *
 * @uses ObjectIdInterface
 *
 * @template-extends AbstractMetadataCapableAdapter<ExtMongoDbOptions,Metadata>
 */
final class ExtMongoDb extends AbstractMetadataCapableAdapter implements FlushableInterface
{
    /**
     * Has this instance be initialized
     */
    private bool $initialized = false;

    /**
     * the mongodb resource manager
     */
    private ?ExtMongoDbResourceManagerInterface $resourceManager = null;

    /**
     * The mongodb resource id
     */
    private ?string $resourceId = null;

    /**
     * The namespace prefix
     */
    private string $namespacePrefix = '';

    /**
     * @param null|iterable<string,mixed>|ExtMongoDbOptions $options
     */
    public function __construct(null|iterable|ExtMongoDbOptions $options = null)
    {
        parent::__construct($options);

        $initialized = &$this->initialized;

        $this->getEventManager()->attach(
            'option',
            static function () use (&$initialized): void {
                $initialized = false;
            }
        );
    }

    /**
     * get mongodb resource
     */
    private function getMongoCollection(): Collection
    {
        $this->initialize();
        $resourceId = $this->resourceId;
        assert($resourceId !== null);
        return $this->resourceManager->getResource($resourceId);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptions(iterable|AdapterOptions $options): self
    {
        if (! $options instanceof ExtMongoDbOptions) {
            $options = new ExtMongoDbOptions($options);
        }

        parent::setOptions($options);
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(): ExtMongoDbOptions
    {
        $options = parent::getOptions();
        if (! $options instanceof ExtMongoDbOptions) {
            $options = new ExtMongoDbOptions($options->toArray());
            $this->setOptions($options);
        }

        return $options;
    }

    /**
     * {@inheritDoc}
     */
    protected function internalGetItem(string $normalizedKey, ?bool &$success = null, mixed &$casToken = null): mixed
    {
        $result  = $this->fetchFromCollection($normalizedKey);
        $success = false;

        if (null === $result) {
            return null;
        }

        if (self::ensureArrayType($result) === false) {
            throw new Exception\RuntimeException(
                'Unable to retrieve item from collection.'
                . ' Document was expected to returned as an array but an object was returned instead.',
            );
        }

        if (isset($result['expires'])) {
            if (! $result['expires'] instanceof MongoDate) {
                throw new Exception\RuntimeException(sprintf(
                    "The found item _id '%s' for key '%s' is not a valid cache item"
                    . ": the field 'expired' isn't an instance of MongoDate, '%s' found instead",
                    (string) $result['_id'],
                    $this->namespacePrefix . $normalizedKey,
                    get_debug_type($result['expires'])
                ));
            }

            if ($result['expires']->toDateTime() < (new MongoDate())->toDateTime()) {
                $this->internalRemoveItem($normalizedKey);
                return null;
            }
        }

        if (! array_key_exists('value', $result)) {
            throw new Exception\RuntimeException(sprintf(
                "The found item _id '%s' for key '%s' is not a valid cache item: missing the field 'value'",
                (string) $result['_id'],
                $this->namespacePrefix . $normalizedKey
            ));
        }

        $success = true;

        return $casToken = $result['value'];
    }

    /**
     * @psalm-assert-if-true array{_id:ObjectIdInterface,...} $result
     */
    private static function ensureArrayType(mixed &$result): bool
    {
        if ($result instanceof ArrayObject) {
            $result = $result->getArrayCopy();
        }

        if (! is_array($result)) {
            return false;
        }

        if (! array_key_exists('_id', $result) || ! $result['_id'] instanceof ObjectIdInterface) {
            throw new Exception\InvalidArgumentException(
                'Provided document does not contain the object ID in the expected format.',
            );
        }

        $result = array_map(self::recursivelyResolveArrayObjects(...), $result);

        return true;
    }

    private static function recursivelyResolveArrayObjects(mixed $value): mixed
    {
        if (! is_iterable($value)) {
            return $value;
        }

        if ($value instanceof ArrayObject) {
            return array_map(self::recursivelyResolveArrayObjects(...), $value->getArrayCopy());
        }

        return $value;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    protected function internalSetItem(string $normalizedKey, mixed $value): bool
    {
        $mongo     = $this->getMongoCollection();
        $key       = $this->namespacePrefix . $normalizedKey;
        $ttl       = $this->getOptions()->getTTl();
        $cacheItem = [
            'key'   => $key,
            'value' => $value,
        ];

        if ($ttl > 0) {
            $ttlSeconds           = (int) round((microtime(true) + $ttl) * 1000);
            $cacheItem['expires'] = new MongoDate($ttlSeconds);
        }

        try {
            $mongo->deleteOne(['key' => $key]);
            $result = $mongo->insertOne($cacheItem);
        } catch (MongoDriverException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return null !== $result && $result->isAcknowledged();
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\RuntimeException
     */
    protected function internalRemoveItem(string $normalizedKey): bool
    {
        try {
            $result = $this->getMongoCollection()->deleteOne(['key' => $this->namespacePrefix . $normalizedKey]);
        } catch (MongoDriverException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return null !== $result && $result->getDeletedCount() > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function flush(): bool
    {
        $result = (object) $this->getMongoCollection()->drop();
        return 1.0 === $result->ok;
    }

    /**
     * {@inheritDoc}
     */
    protected function internalGetCapabilities(): Capabilities
    {
        return $this->capabilities ??= new Capabilities(
            255,
            true,
            true,
            [
                'NULL'     => true,
                'boolean'  => true,
                'integer'  => true,
                'double'   => true,
                'string'   => true,
                'array'    => true,
                'object'   => false,
                'resource' => false,
            ],
            1,
            false,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\ExceptionInterface
     */
    protected function internalGetMetadata(string $normalizedKey): Metadata|null
    {
        $result = $this->fetchFromCollection($normalizedKey);
        if ($result === null) {
            return null;
        }

        if (self::ensureArrayType($result) === false) {
            throw new Exception\RuntimeException(
                'Unable to retrieve item from collection.'
                . ' Document was expected to returned as an array but an object was returned instead.',
            );
        }

        $id = (string) $result['_id'];
        assert($id !== '');

        return new Metadata($id);
    }

    /**
     * Return raw records from MongoCollection
     *
     * @throws Exception\RuntimeException
     */
    private function fetchFromCollection(string $normalizedKey): array|null|object
    {
        try {
            return $this->getMongoCollection()->findOne(['key' => $this->namespacePrefix . $normalizedKey]);
        } catch (MongoDriverException $e) {
            throw new Exception\RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $options = $this->getOptions();

        $this->resourceManager = $options->getResourceManager();
        $this->resourceId      = $options->getResourceId();
        $namespace             = $options->getNamespace();
        $this->namespacePrefix = $namespace === '' ? '' : $namespace . $options->getNamespaceSeparator();
        $this->initialized     = true;
    }
}
