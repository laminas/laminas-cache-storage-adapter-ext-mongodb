<?php

declare(strict_types=1);

namespace Laminas\Cache\Storage\Adapter\ExtMongoDb;

use Interop\Container\ContainerInterface;
use Laminas\Cache\Storage\Adapter\ExtMongoDb;
use Laminas\Cache\Storage\AdapterPluginManager;
use Laminas\ServiceManager\Factory\InvokableFactory;

use function assert;

final class AdapterPluginManagerDelegatorFactory
{
    public function __invoke(ContainerInterface $container, string $name, callable $callback): AdapterPluginManager
    {
        $pluginManager = $callback();
        assert($pluginManager instanceof AdapterPluginManager);

        $pluginManager->configure([
            'factories' => [
                ExtMongoDb::class => InvokableFactory::class,
            ],
            'aliases'   => [
                'ext_mongo_db' => ExtMongoDb::class,
                'extmongodb'   => ExtMongoDb::class,
                'extMongoDb'   => ExtMongoDb::class,
                'extMongoDB'   => ExtMongoDb::class,
                'ExtMongoDb'   => ExtMongoDb::class,
                'ExtMongoDB'   => ExtMongoDb::class,
            ],
        ]);

        return $pluginManager;
    }
}
