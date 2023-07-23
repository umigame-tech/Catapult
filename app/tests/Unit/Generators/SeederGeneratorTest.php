<?php

use UmigameTech\Catapult\FileSystem\FileCheckerInterface;
use UmigameTech\Catapult\Generators\SeederGenerator;

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
    $generator = new SeederGenerator(
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
        ->toContain('class UserSeeder extends Seeder');
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
    $generator = new SeederGenerator(
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
        ->toHaveLength(2);

    $generator->generate();
    expect($this->mocked->removed)
        ->toBeArray()
        ->toHaveLength(0);
});

test('remove old files', function () {
    $this->mocked->checker = new class implements FileCheckerInterface {
        public function exists($path): bool
        {
            return true;
        }
    };

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

    $generator = new SeederGenerator(
        [
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked
    );

    $generator->generate();
    expect($this->mocked->removed)
        ->toHaveLength(2);
});
