<?php

use UmigameTech\Catapult\Datatypes\Rules\RuleRequired;
use UmigameTech\Catapult\Datatypes\Rules\RuleType;

test('construct', function () {
    $rule = new RuleRequired(true);

    expect($rule)
        ->getType()->toBe(RuleType::Required)
        ->getValue()->toBeTrue();
});

test('not required', function () {
    $rule = new RuleRequired(false);

    expect($rule)
        ->getValue()->toBeFalse();
});
