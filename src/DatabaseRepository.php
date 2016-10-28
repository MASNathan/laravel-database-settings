<?php

namespace MASNathan\LaravelDatabaseSettings;

use Illuminate\Config\Repository;
use MASNathan\LaravelDatabaseSettings\Models\Setting;

class DatabaseRepository extends Repository
{
    /**
     * Determine if the given configuration value exists.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        $value = Setting::find($key);

        return $value !== null;
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
        $setting = Setting::find($key);

        if (is_null($setting)) {
            return $default;
        }

        return unserialize($setting->value);
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
            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = serialize($value);
            $setting->save();
        }
    }

    /**
     * Get all of the configuration items for the application.
     *
     * @return array
     */
    public function all()
    {
        $settings = Setting::all();

        if (!$settings) {
            return [];
        }

        $keyValueArray = [];
        foreach ($settings as $setting) {
            $keyParts = explode('.', $setting->key);

            $array = unserialize($setting->value);
            foreach (array_reverse($keyParts) as $index => $key) {
                $array = [$key => $array];
            }

            $keyValueArray = array_merge_recursive($keyValueArray, $array);
        }

        return $keyValueArray;
    }
}
