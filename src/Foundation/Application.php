<?php

/**
 * @author simon <simon@crcms.cn>
 * @datetime 2018-11-10 19:18
 *
 * @link http://crcms.cn/
 *
 * @copyright Copyright &copy; 2018 Rights Reserved CRCMS
 */

namespace CrCms\Microservice\Foundation;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Log\LogServiceProvider;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Events\EventServiceProvider;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Foundation\Application as BaseApplication;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Illuminate\Contracts\Foundation\Application as ApplicationContainerContract;

/**
 * Class Application.
 */
class Application extends BaseApplication implements ContainerContract, ApplicationContainerContract
{
    /**
     * @var string
     */
    const MS_VERSION = '0.3.0-dev1';

    /**
     * @var string
     */
    protected $defaultConfigPath;

    /**
     * msVersion.
     *
     * @return string
     */
    public function msVersion(): string
    {
        return static::MS_VERSION;
    }

    /**
     * Register the basic bindings into the container.
     *
     * @return void
     */
    protected function registerBaseBindings()
    {
        static::setInstance($this);

        $this->instance('app', $this);

        $this->instance(Container::class, $this);

        $this->instance(PackageManifest::class, new PackageManifest(
            new Filesystem, $this->basePath(), $this->getCachedPackagesPath()
        ));
    }

    /**
     * Register all of the base service providers.
     *
     * @return void
     */
    protected function registerBaseServiceProviders()
    {
        $this->register(new EventServiceProvider($this));

        $this->register(new LogServiceProvider($this));
    }

    /**
     * @return string
     */
    public function defaultConfigPath($path = ''): string
    {
        if (is_null($this->defaultConfigPath)) {
            $this->defaultConfigPath = realpath(__DIR__.'/../../config');
        }

        return $path ? $this->defaultConfigPath.DIRECTORY_SEPARATOR.$path : $this->defaultConfigPath;
    }

    /**
     * Get the path to the cached services.php file.
     *
     * @return string
     */
    public function getCachedServicesPath()
    {
        return $_ENV['APP_SERVICES_CACHE'] ?? $this->storagePath().'/run-cache/services.php';
    }

    /**
     * Get the path to the cached packages.php file.
     *
     * @return string
     */
    public function getCachedPackagesPath()
    {
        return $_ENV['APP_PACKAGES_CACHE'] ?? $this->storagePath().'/run-cache/packages.php';
    }

    /**
     * Get the path to the configuration cache file.
     *
     * @return string
     */
    public function getCachedConfigPath()
    {
        return $_ENV['APP_CONFIG_CACHE'] ?? $this->storagePath().'/run-cache/config.php';
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer(): void
    {
        parent::bindPathsInContainer();
        $this->instance('path.module', $this->modulePath());
    }

    /**
     * modulePath.
     *
     * @return string
     */
    public function modulePath(): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.'modules';
    }

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function registerConfiguredProviders()
    {
        $providers = Collection::make($this->config['mount.providers'])
            ->partition(function ($provider) {
                return Str::startsWith($provider, 'Illuminate\\') || Str::startsWith($provider, 'CrCms\\Microservice\\');
            });

        $providers->splice(1, 0, [$this->make(PackageManifest::class)->providers()]);

        (new ProviderRepository($this, new Filesystem, $this->getCachedServicesPath()))
            ->load($providers->collapse()->toArray());
    }

    /**
     * Register the core class aliases in the container.
     *
     * @return void
     */
    public function registerCoreContainerAliases()
    {
        foreach ([
                     'app' => [static::class, \Illuminate\Contracts\Container\Container::class, \Illuminate\Contracts\Foundation\Application::class, \Psr\Container\ContainerInterface::class],
                     'cache' => [\Illuminate\Cache\CacheManager::class, \Illuminate\Contracts\Cache\Factory::class],
                     'cache.store' => [\Illuminate\Cache\Repository::class, \Illuminate\Contracts\Cache\Repository::class],
                     'config' => [\Illuminate\Config\Repository::class, \Illuminate\Contracts\Config\Repository::class],
                     'encrypter' => [\Illuminate\Encryption\Encrypter::class, \Illuminate\Contracts\Encryption\Encrypter::class],
                     'db' => [\Illuminate\Database\DatabaseManager::class],
                     'db.connection' => [\Illuminate\Database\Connection::class, \Illuminate\Database\ConnectionInterface::class],
                     'events' => [\Illuminate\Events\Dispatcher::class, \Illuminate\Contracts\Events\Dispatcher::class],
                     'files' => [\Illuminate\Filesystem\Filesystem::class],
                     'filesystem' => [\Illuminate\Filesystem\FilesystemManager::class, \Illuminate\Contracts\Filesystem\Factory::class],
                     'filesystem.disk' => [\Illuminate\Contracts\Filesystem\Filesystem::class],
                     'filesystem.cloud' => [\Illuminate\Contracts\Filesystem\Cloud::class],
                     'hash' => [\Illuminate\Hashing\HashManager::class],
                     'hash.driver' => [\Illuminate\Contracts\Hashing\Hasher::class],
                     'translator' => [\Illuminate\Translation\Translator::class, \Illuminate\Contracts\Translation\Translator::class],
                     'log' => [\Illuminate\Log\LogManager::class, \Psr\Log\LoggerInterface::class],
                     'queue' => [\Illuminate\Queue\QueueManager::class, \Illuminate\Contracts\Queue\Factory::class, \Illuminate\Contracts\Queue\Monitor::class],
                     'queue.connection' => [\Illuminate\Contracts\Queue\Queue::class],
                     'queue.failer' => [\Illuminate\Queue\Failed\FailedJobProviderInterface::class],
                     'redis' => [\Illuminate\Redis\RedisManager::class, \Illuminate\Contracts\Redis\Factory::class],
                     'validator' => [\Illuminate\Validation\Factory::class, \Illuminate\Contracts\Validation\Factory::class],
                 ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }
}
