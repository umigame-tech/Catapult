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
    expect($content)->toBeString();
    expect($content)->toContain('class UserController');
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
