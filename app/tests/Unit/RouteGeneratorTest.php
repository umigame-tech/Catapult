<?php

use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\Generators\RouteGenerator;

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
    $generator = new RouteGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked
    );

    list('content' => $content) = $generator->generateContent();
    expect($content)
        ->toBeString()
        ->toContain("Route::get('users', [UserController::class, 'index'])->name('user.index');");
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
    $generator = new RouteGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
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
