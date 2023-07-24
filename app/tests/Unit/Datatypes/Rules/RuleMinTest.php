<?php

use UmigameTech\Catapult\Datatypes\Rules\RuleMin;
use UmigameTech\Catapult\Datatypes\Rules\RuleType;

test('construct', function () {
    $rule = new RuleMin(10);

    expect($rule)
        ->getType()->toBe(RuleType::Min)
        ->getValue()->toBe(10);
});
