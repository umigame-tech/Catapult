<?php

use UmigameTech\Catapult\Generators\AuthGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generateContent', function () {
    $entity = [
        'name' => 'person',
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
    $generator = new AuthGenerator(
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
        ->toContain('\'model\' => App\Models\Person::class')
        ->toContain('\'provider\' => \'people\'')
        ->toContain("'people' => [
            'provider' => 'people',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],");
});
