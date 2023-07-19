<?php

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
            'entities' => [
                $entity,
            ],
        ],
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
        [
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked
    );

    list('content' => $content) = $generator->generateContent();
    expect($content)
        ->toBeString()
        ->toContain("Route::get('user/login', [UserController::class, 'login'])->name('user.login');")
        ->toContain("Route::post('user/login', [UserController::class, 'loginSubmit'])->name('user.loginSubmit');")
        ->toContain("Route::post('user/logout', [UserController::class, 'logout'])->name('user.logout');");
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
