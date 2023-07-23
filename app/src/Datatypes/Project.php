<?php

namespace UmigameTech\Catapult\Datatypes;

class Project
{
    public string $projectName = '';
    public DataList $entities;

    public function __construct($data) {
        if (empty($data['project_name'])) {
            throw new \Exception('Project name is required');
        }

        $this->projectName = $data['project_name'];
        $this->entities = new DataList(Entity::class, $data['entities'] ?? []);
    }
}
