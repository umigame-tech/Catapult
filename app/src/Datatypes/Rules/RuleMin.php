<?php

namespace UmigameTech\Catapult\Datatypes\Rules;

class RuleMin implements RuleInterface
{
    private int $value = 0;

    public function __construct($data)
    {
        $this->value = $data;
    }

    public function getType(): RuleType
    {
        return RuleType::Min;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
