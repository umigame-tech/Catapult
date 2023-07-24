<?php

use UmigameTech\Catapult\Datatypes\Rules\RuleFactory;
use UmigameTech\Catapult\Datatypes\Rules\RuleType;

test('required', function () {
    $rule = RuleFactory::create('required', true);

    expect($rule)
        ->getType()->toBe(RuleType::Required)
        ->getValue()->toBeTrue();
});

test('min', function () {
    $rule = RuleFactory::create('min', 10);

    expect($rule)
        ->getType()->toBe(RuleType::Min)
        ->getValue()->toBe(10);
});

test('max', function () {
    $rule = RuleFactory::create('max', 30);

    expect($rule)
        ->getType()->toBe(RuleType::Max)
        ->getValue()->toBe(30);
});

test('invalid rule', function () {
    RuleFactory::create('invalid', 30);
})->throws(\Error::class);
