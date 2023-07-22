<?php

namespace UmigameTech\Catapult\Datatypes\Rules;

interface RuleInterface
{
    public function __construct($data);
    public function getType(): RuleType;
    public function getValue(): mixed;
}
