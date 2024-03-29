<?php

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\ModelGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generateContent', function () {
    $entity = [
        'name' => 'user',
        'allowedFor' => ['web', 'api'],
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
    $generator = new ModelGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ]),
        $this->mocked
    );

    list('content' => $content) = $generator->generateContent(new Entity($entity));

    expect($content)
        ->toBeString()
        ->toContain('class User extends Model');
});

test('authenticatable', function () {
    $entity = [
        'name' => 'user',
        'allowedFor' => ['web', 'api'],
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
    $generator = new ModelGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ]),
        $this->mocked
    );

    list('content' => $content) = $generator->generateContent(new Entity($entity));

    expect($content)
        ->toBeString()
        ->toContain('class User extends Authenticatable');
});

test('generate', function () {
    $entity = [
        'name' => 'user',
        'allowedFor' => ['web', 'api'],
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
    $generator = new ModelGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ]),
        $this->mocked
    );

    $generator->generate();

    expect($this->mocked->contents)->toBeArray();
    expect($this->mocked->contents)->toHaveLength(1);
});

test('relation', function () {
    $book = [
        'name' => 'book',
        'allowedFor' => ['everyone'],
        'attributes' => [
            [
                'name' => 'title',
                'type' => 'string',
            ],
            [
                'name' => 'description',
                'type' => 'string',
            ],
        ],
        'belongsTo' => [
            [
                'name' => 'author',
                'type' => 'select',
            ]
        ],
    ];
    $author = [
        'name' => 'author',
        'allowedFor' => ['everyone'],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
            ],
        ],
    ];

    $generator = new ModelGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $book,
                $author,
            ],
        ]),
        $this->mocked
    );

    $generator->generate();

    expect($this->mocked->contents[0])
        ->toContain('public function author()')
        ->toContain('return $this->belongsTo(Author::class);');

    expect($this->mocked->contents[1])
        ->toContain('public function books()')
        ->toContain('return $this->hasMany(Book::class);');
});
