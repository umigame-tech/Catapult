<?php

namespace UmigameTech\Catapult\Datatypes;

use Newnakashima\TypedArray\TypedArray;

class ControllerSubAction
{
    public string $actionMethodName = '';
    public TypedArray $entities;

    public function __construct(string $actionMethodName, TypedArray $entities)
    {
        $this->actionMethodName = $actionMethodName;
        $this->entities = $entities;
    }

    public function argString(): string
    {
        $entities = $this->entities->reverse();
        return implode(', ', $entities
            ->map(
                fn (Entity $entity) => "{$entity->modelName()} \${$entity->name}"
            ));
    }
}
