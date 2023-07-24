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

    /** for views */
    public string $plural = '';

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

    public function modelName(): string
    {
        $cache = '';
        if (!empty($cache)) {
            return $cache;
        }

        $cache = implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $this->name)
        ));

        return $cache;
    }

    public function controllerName(): string
    {
        return $this->modelName() . 'Controller';
    }

    public function apiControllerName(): string
    {
        return 'Api' . $this->controllerName();
    }

    public function dashboardControllerName(): string
    {
        return $this->modelName() . 'DashboardController';
    }

    public function requestName()
    {
        return $this->modelName() . 'Request';
    }

    public function apiRequestName()
    {
        return 'Api' . $this->requestName();
    }

    public function loginRequestName()
    {
        return $this->modelName() . 'LoginRequest';
    }

    public function factoryName()
    {
        return $this->modelName() . 'Factory';
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

    public function seederName(): string
    {
        return $this->modelName() . 'Seeder';
    }
}
