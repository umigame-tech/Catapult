<?php

namespace UmigameTech\Catapult;

class ProjectSettings
{
    private static ProjectSettings $instance;
    private array $storage = [];

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new ProjectSettings();
        }

        return self::$instance;
    }

    public function set($key, $value)
    {
        $this->storage[$key] = $value;
    }

    public function get($key)
    {
        return $this->storage[$key] ?? null;
    }
}
