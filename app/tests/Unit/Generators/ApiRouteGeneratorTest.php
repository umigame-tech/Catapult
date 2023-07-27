<?php

use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\ApiRouteGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {
    $entity = [
        'name' => 'user',
        'allowedFor' => ['everyone'],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
            ],
            [
                'name' => 'email',
                'type' => 'string',
            ],
            [
                'name' => 'password',
                'type' => 'string',
            ],
        ],
    ];
    $generator = new ApiRouteGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ]),
        $this->mocked
    );

    $generator->generate();

    expect($this->mocked->contents)
        ->toHaveLength(1);
});
