<?php

use UmigameTech\Catapult\Datatypes\Attribute;
use UmigameTech\Catapult\Datatypes\AttributeType;

test('construct', function () {
    $attribute = new Attribute([
        'name' => 'title',
        'type' => 'string',
        'rules' => [
            'required' => true,
            'min' => 3,
            'max' => 12,
        ],
    ]);

    expect($attribute)
        ->name->toBe('title')
        ->type->toBe(AttributeType::String)
        ->loginKey->toBeFalse()
        ->rules->toHaveLength(3);
});

test('loginKey', function () {
    $attribute = new Attribute([
        'name' => 'email',
        'type' => 'email',
        'loginKey' => true,
        'rules' => [
            'required' => true,
            'min' => 3,
            'max' => 12,
        ],
    ]);

    expect($attribute)
        ->name->toBe('email')
        ->type->toBe(AttributeType::Email)
        ->loginKey->toBeTrue()
        ->rules->toHaveLength(3);
});
