<?php

use UmigameTech\Catapult\Generators\AuthenticateSetupGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {
    $generator = new AuthenticateSetupGenerator([
        'project_name' => 'test',
        'entities' => [
            [
                'name' => 'person',
                'authenticatable' => true,
                'attributes' => [
                    'name' => 'string',
                    'type' => 'string',
                ],
            ]
        ],
    ], $this->mocked);

    $generator->generate();
    expect($this->mocked->copied)
        ->toBeArray()
        ->toHaveLength(1);

    expect($this->mocked->copied[0]['source'])
        ->toContain('/Templates/app/Http/Middleware/Authenticate.php');
    expect($this->mocked->copied[0]['dest'])
        ->toContain('/app/Http/Middleware/Authenticate.php');
});
