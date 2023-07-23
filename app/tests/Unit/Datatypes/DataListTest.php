<?php

use UmigameTech\Catapult\Datatypes\DataList;
use UmigameTech\Catapult\Datatypes\Entity;

test('entities', function () {
    $dataList = new DataList(Entity::class, [
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
    ]);

    foreach ($dataList as $entity) {
        expect($entity)
            ->toBeInstanceOf(Entity::class);
    }
});
