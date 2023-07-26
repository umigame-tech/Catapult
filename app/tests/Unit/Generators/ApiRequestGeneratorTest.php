<?php

use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\Generators\ApiRequestGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {
    $generator = new ApiRequestGenerator(
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

    expect($this->mocked->copied[0])
        ->toEqual([
            'source' => '/app/src/Generators/../Templates/app/Http/Requests/Api/ApiRequest.php',
            'dest' => '/dist/test/app/Http/Requests/Api/ApiRequest.php',
        ]);
});
