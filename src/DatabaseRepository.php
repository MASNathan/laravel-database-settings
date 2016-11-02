<?php

namespace MASNathan\LaravelDatabaseSettings;

use Illuminate\Config\Repository;
use Illuminate\Support\Arr;
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
        $settings = Setting::where('key', 'LIKE', $key . '%')->get()->keyBy('key');

        if ($settings->count() == 0) {
            return $default;
        }

        if ($settings->count() == 1 && $settings->first()->key == $key) {
            $value = unserialize($settings->first()->value);

            return is_null($value) ? $default : $value;
        }

        $array = [];
        foreach ($settings as $setting) {
            Arr::set($array, $setting->key, unserialize($setting->value));
        }

        return Arr::get($array, $key, $default);
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
            if (is_array($value) && Arr::isAssoc($value)) {
                foreach (Arr::dot($value, $key . '.') as $k => $v) {
                    $setting = Setting::firstOrNew(['key' => $k]);
                    $setting->value = serialize($v);
                    $setting->save();
                }
            } else {
                $setting = Setting::firstOrNew(['key' => $key]);
                $setting->value = serialize($value);
                $setting->save();
            }
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

        $array = [];
        foreach ($settings as $setting) {
            Arr::set($array, $setting->key, unserialize($setting->value));
        }

        return $array;
    }
}
