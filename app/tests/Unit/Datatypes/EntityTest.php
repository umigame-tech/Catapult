<?php

use UmigameTech\Catapult\Datatypes\Entity;

test('construct', function () {
    $entity = new Entity([
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
    ]);

    expect($entity)
        ->name->toBe('book')
        ->allowedFor->toHaveLength(2)
        ->attributes->toHaveLength(2)
        ->isAuthenticatable()->toBeFalse();
});

test('authenticatable', function () {
    $entity = new Entity([
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
                    'min' => 4,
                    'max' => 32,
                ],
            ],
        ],
        'authenticatable' => true,
    ]);

    expect($entity)
        ->name->toBe('person')
        ->allowedFor->toHaveLength(2)
        ->attributes->toHaveLength(3)
        ->isAuthenticatable()->toBeTrue();
});
