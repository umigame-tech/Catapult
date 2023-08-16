<?php

use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\AuthGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generateContent', function () {
    $entity = [
        'name' => 'person',
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
    $generator = new AuthGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ]),
        $this->mocked
    );

    list('content' => $content) = $generator->generateContent();
    expect($content)
        ->toBeString()
        ->toContain('\'model\' => App\Models\Person::class')
        ->toContain('\'provider\' => \'people\'')
        ->toContain("'people' => [
            'provider' => 'people',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],");
});

test('generateSanctumContent', function () {
    $person = [
        'name' => 'person',
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
    $user = [
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
    $generator = new AuthGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $person,
                $user,
            ],
        ]),
        $this->mocked
    );

    list('content' => $content) = $generator->generateSanctumContent();
    expect($content)
        ->toBeString()
        ->toContain("
    'guard' => [
        'people',
        'users',
    ],");
});
