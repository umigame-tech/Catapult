<?php

namespace UmigameTech\Catapult\Datatypes;

use Doctrine\Inflector\InflectorFactory;
use Newnakashima\TypedArray\TypedArray;

class Entity
{
    public string $name = '';
    public array $allowedFor = [];
    public TypedArray $attributes;
    private bool $authenticatable = false;
    public string $dataPath = '';

    /** for routes */
    public array $routes = [];
    public array $loginRoutes = [];

    /** for views */
    public string $plural = '';

    public TypedArray $belongsTo;
    public TypedArray $belongsToEntities;
    public TypedArray $hasManyEntities;

    public function __construct($data) {
        $this->name = $data['name'];
        $this->allowedFor = $data['allowedFor'];

        $this->attributes = new TypedArray(Attribute::class, $data['attributes'] ?? []);
        $this->authenticatable = $data['authenticatable'] ?? false;

        if (!empty($data['dataPath'])) {
            $this->dataPath = $data['dataPath'];
        }

        $this->belongsTo = new TypedArray('string', $data['belongsTo'] ?? []);
        $this->plural = InflectorFactory::create()->build()->pluralize($this->name);
        $this->belongsToEntities = new TypedArray(Entity::class);
        $this->hasManyEntities = new TypedArray(Entity::class);
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

    public function apiStoreRequestName()
    {
        return 'ApiStore' . $this->requestName();
    }

    public function apiUpdateRequestName()
    {
        return 'ApiUpdate' . $this->requestName();
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

    public function resourceName(): string
    {
        return $this->modelName() . 'Resource';
    }

    public function resourceCollectionName(): string
    {
        return $this->modelName() . 'ResourceCollection';
    }

    public function initialDataSeederName(): string
    {
        return "Initial{$this->modelName()}DataSeeder";
    }

    public function hasInitialData(): bool
    {
        return !empty($this->dataPath);
    }

    public function foreignIdName(): string
    {
        return $this->name . '_id';
    }

    public function hasBelongsToEntities(): bool
    {
        return !$this->belongsToEntities->isEmpty();
    }

    public function hasHasManyEntities(): bool
    {
        return !$this->hasManyEntities->isEmpty();
    }

    private function hasManyEntitiesTowardLeafs(): TypedArray
    {
        $entities = new TypedArray(Entity::class);
        foreach ($this->hasManyEntities as $entity) {
            $entities->push($entity);
            if (! $entity->hasManyEntities->isEmpty()) {
                $entities = $entities->merge($entity->hasManyEntitiesTowardLeafs());
            }
        }

        return $entities;
    }

    private function belongsToEntitiesTowardRoot(): TypedArray
    {
        $entities = new TypedArray(Entity::class);
        foreach ($this->belongsToEntities as $entity) {
            $entities->push($entity);
            if (! $entity->belongsToEntities->isEmpty()) {
                $entities = $entities->merge($entity->belongsToEntitiesTowardRoot());
            }
        }

        return $entities;
    }

    public function dependentEntities(): TypedArray
    {
        $hasManyEntitiesTowardLeafs = $this->hasManyEntitiesTowardLeafs();
        $belongsToEntitiesTowardRoot = $this->belongsToEntitiesTowardRoot();
        return $hasManyEntitiesTowardLeafs->merge($belongsToEntitiesTowardRoot);
    }
}
