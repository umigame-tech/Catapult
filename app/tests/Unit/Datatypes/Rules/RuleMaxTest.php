<?php

use UmigameTech\Catapult\Datatypes\Rules\RuleMax;
use UmigameTech\Catapult\Datatypes\Rules\RuleType;

test('construct', function () {
    $rule = new RuleMax(30);

    expect($rule)
        ->getType()->toBe(RuleType::Max)
        ->getValue()->toBe(30);
});
