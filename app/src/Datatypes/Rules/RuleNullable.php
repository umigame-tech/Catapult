<?php

namespace UmigameTech\Catapult\Datatypes\Rules;

class RuleNullable implements RuleInterface
{
    private bool $value;
    public function __construct($data)
    {
        $this->value = $data;
    }

    public function getType(): RuleType
    {
        return RuleType::Nullable;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}
