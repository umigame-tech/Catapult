<?php

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\ControllerGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generateContent', function () {
    $entity = [
        'name' => 'user',
        'allowedFor' => ['user', 'admin'],
        'fields' => [
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
    $generator = new ControllerGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ]),
        $this->mocked,
    );

    $controller = $generator->generateContent(new Entity($entity));
    $path = $controller['path'];
    $content = $controller['content'];
    expect($path)->toBeString();
    expect($content)
        ->toBeString()
        ->toContain('class UserController')
        ->not->toContain('public function login');
});

test('authenticatable', function () {
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
                'loginKey' => true,
            ],
            [
                'name' => 'password',
                'type' => 'password',
            ],
        ],
    ];
    $generator = new ControllerGenerator(
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
        ->toContain('class UserController')
        ->toContain('public function login(')
        ->toContain('public function loginSubmit(')
        ->toContain('public function logout(')
        ->toContain('$credentials = $request->only(\'email\', \'password\');');
});

test('generate', function () {
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
    $generator = new ControllerGenerator(
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

test('subActions', function () {
    $author = [
        'name' => 'author',
        'allowedFor' => ['everyone'],
        'attributes' => [
            [
                'name' => 'name',
                'type' => 'string',
            ],
            [
                'name' => 'birth_year',
                'type' => 'integer',
            ]
        ],
    ];

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
        'belongsTo' => ['author'],
    ];

    $chapter = [
        'name' => 'chapter',
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
        'belongsTo' => ['book'],
    ];

    $project = new Project([
        'project_name' => 'test',
        'entities' => [
            $author,
            $book,
            $chapter,
        ],
    ]);

    $generator = new ControllerGenerator(
        $project,
        $this->mocked
    );

    $actions = $generator->subActions($project->entities[2]);
    expect($actions->map(fn ($action) => $action['actionMethodName']))
        ->toBeArray()
        ->toContain('author_book_chapter_index')
        ->toContain('book_chapter_index');

    $entities = $actions->map(fn ($action) => $action['entities'])[$actions->count() - 1];
    $entityNames = $entities->map(fn ($entity) => $entity->name);
    expect($entityNames)
        ->toBeArray()
        ->toContain('chapter', 'book', 'author');
});
