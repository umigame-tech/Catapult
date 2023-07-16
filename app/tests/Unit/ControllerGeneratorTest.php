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
            'sealed_prefix' => 'admin',
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
                'type' => 'password',
            ],
        ],
    ];
    $generator = new ControllerGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
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
        ->toContain('public function logout(');
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
            'sealed_prefix' => 'admin',
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
