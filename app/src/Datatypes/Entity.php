<?php

namespace UmigameTech\Catapult\Datatypes;

class Entity
{
    public string $name = '';
    public array $allowedFor = [];
    public array $attributes = [];
    private bool $authenticatable = false;

    public function __construct($data) {
        $this->name = $data['name'];
        $this->allowedFor = $data['allowedFor'];
        foreach ($data['attributes'] ?? [] as $attribute) {
            $this->attributes[] = new Attribute($attribute);
        }
        $this->authenticatable = $data['authenticatable'] ?? false;
    }

    public function isAuthenticatable(): bool
    {
        return $this->authenticatable;
    }
}
