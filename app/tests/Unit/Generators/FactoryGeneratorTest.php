<?php

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\FactoryGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generateContent', function () {
    $entity = [
        'name' => 'user',
        'allowedFor' => ['user', 'admin'],
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
    $generator = new FactoryGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ]),
        $this->mocked,
    );

    list('content' => $content) = $generator->generateContent(new Entity($entity));

    expect($content)->toBeString();
});

test('password', function () {
    $entity = [
        'name' => 'user',
        'allowedFor' => ['user', 'admin'],
        'authenticatable' => true,
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
                'type' => 'password',
            ],
        ],
    ];
    $generator = new FactoryGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ]),
        $this->mocked,
    );

    list('content' => $content) = $generator->generateContent(new Entity($entity));

    expect($content)
        ->toBeString()
        ->toContain()
        ->toContain("'password' => Hash::make(");
});
