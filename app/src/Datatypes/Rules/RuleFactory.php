<?php

namespace UmigameTech\Catapult\Datatypes\Rules;

class RuleFactory
{
    public static function create(string $type, $data): RuleInterface
    {
        $type = RuleType::from($type);
        return match ($type) {
            RuleType::Required => new RuleRequired($data),
            RuleType::Min => new RuleMin($data),
            RuleType::Max => new RuleMax($data),
            default => throw new \Exception('Invalid rule type'),
        };
    }
}
