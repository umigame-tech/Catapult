<?php

use UmigameTech\Catapult\Generators\FactoryGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generateContent', function () {
    $entity = [
        'name' => 'user',
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
        [
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked,
    );

    list('content' => $content) = $generator->generateContent($entity);

    expect($content)->toBeString();
});

test('password', function () {
    $entity = [
        'name' => 'user',
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
        [
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked,
    );

    list('content' => $content) = $generator->generateContent($entity);

    expect($content)
        ->toBeString()
        ->toContain()
        ->toContain("'password' => Hash::make(");
});
