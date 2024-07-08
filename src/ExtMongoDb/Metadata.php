<?php

declare(strict_types=1);

namespace Laminas\Cache\Storage\Adapter\ExtMongoDb;

final class Metadata
{
    /**
     * @param non-empty-string $objectId
     */
    public function __construct(
        public readonly string $objectId,
    ) {
    }
}
