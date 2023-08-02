<?php

use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\ApiStoreRequestGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {
    $generator = new ApiStoreRequestGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [
                [
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
                ],
            ],
        ]),
        $this->mocked
    );

    $generator->generate();

    expect($this->mocked->copied)
        ->toHaveLength(1);

    expect($this->mocked->copied[0]['source'])
        ->toContain('/Templates/app/Http/Requests/Api/ApiRequest.php');
    expect($this->mocked->copied[0]['dest'])
        ->toContain('/dist/test/app/Http/Requests/Api/ApiRequest.php');

    expect($this->mocked->contents[0])
        ->toContain('class ApiStoreUserRequest extends ApiRequest');
});
