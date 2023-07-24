<?php

namespace UmigameTech\Catapult\Datatypes;

use Doctrine\Inflector\InflectorFactory;

class Entity
{
    public string $name = '';
    public array $allowedFor = [];
    public DataList $attributes;
    private bool $authenticatable = false;

    /** for routes */
    public array $routes = [];
    public array $loginRoutes = [];

    public function __construct($data) {
        $this->name = $data['name'];
        $this->allowedFor = $data['allowedFor'];

        $this->attributes = new DataList(Attribute::class, $data['attributes'] ?? []);
        $this->authenticatable = $data['authenticatable'] ?? false;
    }

    public function isAuthenticatable(): bool
    {
        return $this->authenticatable;
    }

    public function controllerName(): string
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $this->name)
        )) . 'Controller';
    }

    public function dashboardControllerName(): string
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $this->name)
        )) . 'DashboardController';
    }

    public function modelName()
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $this->name)
        ));
    }

    public function requestName()
    {
        return $this->modelName() . 'Request';
    }

    public function loginRequestName()
    {
        return $this->modelName() . 'LoginRequest';
    }

    public function factoryName()
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $this->name)
        )) . 'Factory';
    }

    public function authName(): string
    {
        static $cache = '';
        if (!empty($cache)) {
            return $cache;
        }

        $inflector = InflectorFactory::create()->build();
        $cache = $inflector->pluralize($this->name);
        return $cache;
    }
}
