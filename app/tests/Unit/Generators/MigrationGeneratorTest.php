<?php

use UmigameTech\Catapult\Generators\MigrationGenerator;

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
    $generator = new MigrationGenerator(
        [
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked
    );

    list('content' => $content) = $generator->generateContent($entity);

    expect($content)
        ->toBeString()
        ->toContain('Schema::create(\'users\', function (Blueprint $table) {');
});

test('generate', function () {
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
    $generator = new MigrationGenerator(
        [
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked
    );

    $generator->generate();

    expect($this->mocked->contents)
        ->toBeArray()
        ->toHaveLength(1);
});
