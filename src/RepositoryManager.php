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

        parent::__construct($this->config->all());

        $this->load();
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

            // Set to instance
            parent::set($key, $value);
        }

        $this->save();
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

    protected function load()
    {
        if ($this->cache->has(RepositoryManager::class)) {
            $this->items = $this->cache->get(RepositoryManager::class);
        } else {
            $this->items = $this->all();
        }
    }

    protected function save()
    {
        // Save to Cache
        if (in_array(get_class($this->cache->getStore()), [FileStore::class, DatabaseStore::class])) {
            // Tagging is not supported for these drivers
            $this->cache->put(RepositoryManager::class, $this->items, $this->get('settings.cache.ttl'));
        } else {
            $this->cache->tags(['config', 'app'])->put(RepositoryManager::class, $this->items, $this->get('settings.cache.ttl'));
        }
    }
}
