<?php

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\Generators\InitialDataSeederGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
    $reader = new class implements FileReaderInterface {
        public function read($path)
        {
            if (preg_match('/test_data/', $path)) {
                return json_encode([
                    [
                        'level' => 1,
                        'name' => 'admin',
                    ],
                    [
                        'level' => 2,
                        'name' => 'user',
                    ],
                    [
                        'level' => 3,
                        'name' => 'guest',
                    ]
                ]);
            }

            return "";
        }
    };

    $this->mocked->reader = $reader;
});

test('generateContent', function () {
    $entity = [
        'name' => 'role',
        'allowedFor' => ['admin'],
        'attributes' => [
            [
                'name' => 'level',
                'type' => 'integer',
            ],
            [
                'name' => 'name',
                'type' => 'string',
            ],
        ],
        'dataPath' => "test_data",
    ];
    $generator = new InitialDataSeederGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [ $entity ],
        ]),
        $this->mocked,
    );

    $content = $generator->generateContent(new Entity($entity));

    expect($content['content'])
        ->toContain('class InitialRoleDataSeeder extends Seeder')
        ->toContain("'level' => '1',")
        ->toContain("'name' => 'admin',")
        ->toContain("'level' => '2',")
        ->toContain("'name' => 'user',")
        ->toContain("'level' => '3',")
        ->toContain("'name' => 'guest',");
});

test('generate', function () {
    $entity = [
        'name' => 'role',
        'allowedFor' => ['admin'],
        'attributes' => [
            [
                'name' => 'level',
                'type' => 'integer',
            ],
            [
                'name' => 'name',
                'type' => 'string',
            ],
        ],
        'dataPath' => "test_data",
    ];
    $generator = new InitialDataSeederGenerator(
        new Project([
            'project_name' => 'test',
            'entities' => [ $entity ],
        ]),
        $this->mocked,
    );

    $generator->generate();

    expect($this->mocked->contents[0])
        ->toContain('class InitialRoleDataSeeder extends Seeder')
        ->toContain("'level' => '1',")
        ->toContain("'name' => 'admin',")
        ->toContain("'level' => '2',")
        ->toContain("'name' => 'user',")
        ->toContain("'level' => '3',")
        ->toContain("'name' => 'guest',");
});
