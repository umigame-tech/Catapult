<?php
use UmigameTech\Catapult\Generators\ControllerGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generateContent', function () {
    $entity = [
        'name' => 'user',
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
        [
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked,
    );

    $controller = $generator->generateContent($entity);
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
        ->toContain('class UserController')
        ->toContain('public function login(')
        ->toContain('public function loginSubmit(')
        ->toContain('public function logout(')
        ->toContain('$credentials = $request->only(\'email\', \'password\');');
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
    $generator = new ControllerGenerator(
        [
            'project_name' => 'test',
            'entities' => [
                $entity,
            ],
        ],
        $this->mocked
    );
    $generator->generate();

    expect($this->mocked->contents)->toBeArray();
    expect($this->mocked->contents)->toHaveLength(1);
});

test('dashboardControllerName', function () {
    $entity = [
        'name' => 'foo_bar',
        'authenticatable' => true,
        'attributes' => [
            [
                'name' => 'id',
                'type' => 'integer',
            ],
            [
                'name' => 'name',
                'type' => 'string',
            ],
        ],
    ];
    $generator = new ControllerGenerator([
        'entities' => [
            $entity,
        ],
    ], $this->mocked);
    expect($generator::dashboardControllerName($entity))->toBe('FooBarDashboardController');

    $entity['name'] = 'foo_bar_baz';
    expect($generator::dashboardControllerName($entity))->toBe('FooBarBazDashboardController');
});
