<?php

namespace UmigameTech\Catapult\Datatypes\Rules;

class RuleRequired implements RuleInterface
{
    private bool $value = false;

    public function __construct($data)
    {
        $this->value = $data;
    }

    public function getType(): RuleType
    {
        return RuleType::Required;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
