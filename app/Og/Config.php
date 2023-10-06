<?php

namespace LpdPromo\Og;

class Config
{
    /**
     * Raw config array.
     */
    protected array $data;

    /**
     * Forbidden keys.
     * @var array
     */
    private static array $reserved_keys = ['namespace', 'type', 'version', 'author', 'addons', 'license'];

    /**
     * Static cache.
     * @var array
     */
    private static array $cache = [];

    /**
     * Constructor.
     * @param array $data Raw config array.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Fetch a value by key.
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        // Use cached value if exists.
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        if (defined($key) && !in_array($key, self::$reserved_keys)) {
            return constant($key);
        }

        $value = $this->get_value_from_keys(explode('.', $key), $this->data);

        // Cache the result.
        self::$cache[$key] = $value;

        return $value;
    }

    /**
     * Recursive function to get the value from the keys.
     * @param array $keys
     * @param array $sub
     * @return mixed|null
     */
    private function get_value_from_keys(array $keys, array $sub)
    {
        if (empty($keys)) {
            return null;
        }

        $current_key = array_shift($keys);

        if (!isset($sub[$current_key])) {
            return null;
        }

        if (empty($keys)) {
            return $sub[$current_key];
        }

        return $this->get_value_from_keys($keys, $sub[$current_key]);
    }
}
