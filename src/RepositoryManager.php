<?php

namespace MASNathan\LaravelDatabaseSettings;

use Illuminate\Cache\DatabaseStore;
use Illuminate\Cache\FileStore;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Cache\Repository as CacheRepository;

class RepositoryManager extends ConfigRepository
{
    /**
     * @var CacheRepository
     */
    protected $cache;

    /**
     * @var DatabaseRepository
     */
    protected $database;

    /**
     * @var ConfigRepository
     */
    protected $config;

    public function __construct()
    {
        $this->cache = app('cache');
        $this->config = app('config.local');
        $this->database = app('config.database');

        $items = [];
        if ($this->config->has('settings')) {
            $items['settings'] = $this->config->get('settings');
        }
        if ($this->config->has('settings.excluded')) {
            foreach ($this->config->get('settings.excluded') as $key) {
                $items[$key] = $this->config->get($key);
            }
        }

        if (isset($items['app']['name'])) {
            unset($items['app']['name']);
        }
        if (isset($items['app']['timezone'])) {
            unset($items['app']['timezone']);
        }
        if (isset($items['app']['locale'])) {
            unset($items['app']['locale']);
        }
        parent::__construct($items);
    }

    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        // Check loaded keys
        if (parent::has($key)) {
            return true;
        }

        // Check cache keys
        if ($this->cache->has($key)) {
            parent::set($key, $this->cache->get($key));

            return true;
        }

        // Check database keys
        if ($this->database->has($key)) {
            parent::set($key, $this->database->get($key));

            return true;
        }

        // Check config keys
        if ($this->config->has($key)) {
            parent::set($key, $this->config->get($key));

            return true;
        }

        return false;
    }

    /**
     * Get the specified configuration value.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        // Check loaded keys
        if (parent::has($key)) {
            return parent::get($key, $default);
        }

        // Check cache keys
        if ($this->cache->has($key)) {
            parent::set($key, $this->cache->get($key));

            return parent::get($key);
        }

        // Check database keys
        if ($value = $this->database->get($key)) {
            parent::set($key, $value);

            return parent::get($key);
        }

        // Check config keys
        if ($value = $this->config->get($key)) {
            parent::set($key, $value);

            return parent::get($key);
        }

        return $default;
    }

    /**
     * Set a given configuration value.
     *
     * @param  array|string $key
     * @param  mixed        $value
     * @return void
     */
    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            // Set to Database
            $this->database->set($key, $value);

            // Set to Cache
            if (in_array(get_class($this->cache->getStore()), [FileStore::class, DatabaseStore::class])) {
                // Tagging is not supported for these drivers
                $this->cache->put($key, $value, $this->get('settings.cache.ttl'));
            } else {
                $this->cache->tags(['config', 'app'])->put($key, $value, $this->get('settings.cache.ttl'));
            }

            // Set to instance
            parent::set($key, $value);
        }
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        return array_replace_recursive($this->config->all(), $this->database->all());
    }
}
