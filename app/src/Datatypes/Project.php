<?php

namespace UmigameTech\Catapult\Datatypes;
use Newnakashima\TypedArray\TypedArray;

class Project
{
    public string $projectName = '';
    public TypedArray $entities;

    public function __construct($data) {
        if (empty($data['project_name'])) {
            throw new \Exception('Project name is required');
        }

        $this->projectName = $data['project_name'];
        $this->entities = new TypedArray(Entity::class, $data['entities'] ?? []);
    }
}
