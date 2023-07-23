<?php

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Project;

test('project', function () {
    $project = new Project([
        'project_name' => 'test',
        'entities' => [
            [
                'name' => 'book',
                'allowedFor' => ['admin', 'user'],
                'attributes' => [
                    [
                        'name' => 'title',
                        'type' => 'string',
                        'loginKey' => false,
                        'rules' => [
                            'required' => true,
                        ],
                    ],
                    [
                        'name' => 'author',
                        'type' => 'string',
                        'loginKey' => false,
                        'rules' => [
                            'required' => true,
                        ],
                    ],
                ],
            ],
            [
                'name' => 'person',
                'allowedFor' => ['admin', 'user'],
                'attributes' => [
                    [
                        'name' => 'name',
                        'type' => 'string',
                        'loginKey' => false,
                        'rules' => [
                            'required' => true,
                        ],
                    ],
                    [
                        'name' => 'email',
                        'type' => 'email',
                        'loginKey' => true,
                        'rules' => [
                            'required' => true,
                        ],
                    ],
                    [
                        'name' => 'password',
                        'type' => 'password',
                        'rules' => [
                            'required' => true,
                        ],
                    ],
                ],
            ]
        ]
    ]);

    expect($project)
        ->projectName->toBe('test')
        ->entities->toHaveLength(2)
        ->each->toBeInstanceOf(Entity::class);
});
