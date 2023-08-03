<?php

use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\RouteGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generateContent', function () {
    $entity = [
        'name' => 'user',
        'allowedFor' =>['everyone'],
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
        ->toContain("Route::get('users', [UserController::class, 'index'])->name('user.index');")
        ->not->toContain("Route::get('user/login', [UserController::class, 'login'])->name('user.login');");
});

test('authenticatable', function () {
    $entity = [
        'name' => 'user',
        'authenticatable' => true,
        'allowedFor' => [
            'user',
        ],
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
    $generator = new RouteGenerator(
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
        ->toContain("Route::prefix('users')->name('users.')->middleware('auth:users')->group(function () {")
        ->toContain("    Route::get('users', [UserController::class, 'index'])->name('user.index');")
        ->toContain("    Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');")
        ->toContain("Route::get('users/login', [UserController::class, 'login'])->name('users.login');")
        ->toContain("Route::post('users/login', [UserController::class, 'loginSubmit'])->name('users.loginSubmit');")
        ->toContain("Route::get('users/logout', [UserController::class, 'logout'])->name('users.logout');");
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
    $generator = new RouteGenerator(
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
        ->toBeArray()
        ->toHaveLength(1);
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
        'belongsTo' => ['author'],
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

    $generator = new RouteGenerator(
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

    var_dump($this->mocked->contents);

    expect(true)->toBeTrue();
});
