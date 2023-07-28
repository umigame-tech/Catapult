<?php

use UmigameTech\Catapult\Datatypes\Rules\RuleNullable;
use UmigameTech\Catapult\Datatypes\Rules\RuleType;

test('construct', function () {
    $rule = new RuleNullable(true);

    expect($rule)
        ->getType()->toBe(RuleType::Nullable)
        ->getValue()->toBeTrue();
});

test('not nullable', function () {
    $rule = new RuleNullable(false);

    expect($rule)
        ->getValue()->toBeFalse();
});
