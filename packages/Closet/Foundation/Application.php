<?php

namespace Closet\Foundation;

use Illuminate\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class ContainerValueNotFoundException extends RuntimeException implements NotFoundExceptionInterface
{
}

class Application extends Container implements ContainerInterface
{
    /**
     * The base path for the app installation.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The custom database path defined by the developer.
     *
     * @var string
     */
    protected $databasePath;

    /**
     * The custom storage path defined by the developer.
     *
     * @var string
     */
    protected $storagePath;

    /**
     * @return self
     */
    public static function create($path = null)
    {
        $instance = new static;

        !$path ?: $instance->setBasePath(realpath($path));

        return $instance;
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function bindPathsInContainer()
    {
        $this->offsetSet('path', $this->path());

        foreach (array('base', 'config', 'database', 'lang', 'public', 'storage') as $path) {
            $this->offsetSet('path.' . $path, $this->{$path . 'Path'}());
        }

        // load configurations

        $this->offsetSet('config', $config = new Config);

        $configPath = $this->configPath();

        // Get the configuration file nesting path.
        $nestingCallback = function ($file, $configPath) {
            if ($tree = trim(str_replace($configPath, '', dirname($file)), '\\/')) {
                $tree = str_replace(DIRECTORY_SEPARATOR, '.', $tree) . '.';
            }
            return $tree;
        };

        $files = [];
        if ($handle = opendir($configPath)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $files[] = realpath($configPath . '/' . $entry);
                }
            }
            closedir($handle);
        }

        foreach ($files as $file) {
            $file = (string) $file;
            $config->set($nestingCallback($file, $configPath) . basename($file, '.php'), require $file);
        }

        if ($timezone = $config['app.timezone']) {
            date_default_timezone_set($timezone);
        }

        mb_internal_encoding('UTF-8');

        return $this;
    }

    /**
     * Set the base path for the application.
     *
     * @param  string  $basePath
     * @return $this
     */
    public function setBasePath($basePath)
    {
        !$basePath ?: $this->basePath = $basePath;

        $this->bindPathsInContainer();

        return $this;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'app';
    }

    /**
     * Get the base path of the Laravel installation.
     *
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }

    /**
     * Get the path to the application configuration files.
     *
     * @return string
     */
    public function configPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config';
    }

    /**
     * Get the path to the database directory.
     *
     * @return string
     */
    public function databasePath()
    {
        return $this->databasePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'database';
    }

    /**
     * Set the database directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function useDatabasePath($path)
    {
        $this->databasePath = $path;

        $this->offsetSet('path.database', $path);

        return $this;
    }

    /**
     * Get the path to the language files.
     *
     * @return string
     */
    public function langPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang';
    }

    /**
     * Get the path to the public / web directory.
     *
     * @return string
     */
    public function publicPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->storagePath ?: $this->basePath . DIRECTORY_SEPARATOR . 'storage';
    }

    /**
     * Set the storage directory.
     *
     * @param  string  $path
     * @return $this
     */
    public function useStoragePath($path)
    {
        $this->storagePath = $path;

        $this->offsetSet('path.storage', $path);

        return $this;
    }

    /**
     * Get the path to the resources directory.
     *
     * @param  string   $path
     * @return string
     */
    public function resourcePath($path = '')
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     */
    public function runningInConsole()
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * Get the current application locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this['config']->get('app.locale');
    }

    /**
     * Set the current application locale.
     *
     * @param  string $locale
     * @return void
     */
    public function setLocale($locale)
    {
        $this['config']->set('app.locale', $locale);

        if ($this->offsetExists('translator')) {
            $this->offsetGet('translator')->setLocale($locale);
        }

        $this->emit('locale.changed', array($locale));
    }

    /**
     * Get the translation for a given key from the JSON translation files.
     *
     * @param  string  $key
     * @param  array  $replace
     * @param  string  $locale
     * @return string|array
     */
    public function translate($key)
    {
        return call_user_func_array([$this['translator'], 'getFromJson'], func_get_args());
    }

    public function trans($key)
    {
        return $this->translate($key);
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  string   $event
     * @param  callable $listener
     * @return self
     */
    public function on($event, $listener)
    {
        $events = $this->offsetGet('events');

        $method = method_exists($events, __FUNCTION__) ? __FUNCTION__ : 'listen';

        call_user_func_array(array($events, $method), func_get_args());

        return $this;
    }

    /**
     * @param  string   $event
     * @param  mixed    $arguments
     * @return mixed
     */
    public function emit($event)
    {
        $events = $this->offsetGet('events');

        $method = method_exists($events, __FUNCTION__) ? __FUNCTION__ : null;

        if (!$method) {
            $method = method_exists($events, 'dispatch') ? 'dispatch' : 'fire';
        }

        $arguments = array_slice(func_get_args(), 1);

        if (is_array($arg = current($arguments))) {
            $arguments = $arg;
        }

        return call_user_func_array(
            array($events, $method), array($event, $arguments)
        );
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return boolean
     */
    public function has($id)
    {
        return $this->offsetExists($id);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return mixed
     *
     * @throws InvalidArgumentException         Thrown when an offset cannot be found in the Pimple container
     * @throws SlimContainerException           Thrown when an exception is
     *         not an instance of ContainerExceptionInterface
     * @throws ContainerValueNotFoundException  No entry was found for this identifier.
     * @throws ContainerExceptionInterface      Error while retrieving the entry.
     */
    public function get($id)
    {
        if (!$this->offsetExists($id)) {
            throw new ContainerValueNotFoundException(sprintf('Identifier "%s" is not defined.', $id));
        }
        try {
            return $this->offsetGet($id);
        } catch (\InvalidArgumentException $exception) {
            throw $exception;
        }
    }
}
