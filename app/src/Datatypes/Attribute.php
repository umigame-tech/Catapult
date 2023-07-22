<?php

namespace UmigameTech\Catapult\Datatypes;

use UmigameTech\Catapult\Datatypes\Rules\RuleFactory;

class attribute
{
    public string $name = '';
    public AttributeType $type;
    public bool $loginKey = false;
    /** @var RuleInterface[] */
    public array $rules = [];
    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->loginKey = $data['loginKey'];

        $this->type = AttributeType::from($data['type']);

        foreach ($data['rules'] ?? [] as $type => $rule) {
            $this->rules[] = RuleFactory::create($type, $rule);
        }
    }
}
