<?php

namespace UmigameTech\Catapult\Datatypes;

use UmigameTech\Catapult\Datatypes\Rules\RuleFactory;
use UmigameTech\Catapult\Datatypes\Rules\RuleInterface;

class Attribute
{
    public string $name = '';
    public AttributeType $type;
    public bool $loginKey = false;
    /** @var RuleInterface[] */
    public DataList $rules;
    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->loginKey = $data['loginKey'] ?? false;

        $this->type = AttributeType::from($data['type']);

        $this->rules = new DataList(RuleInterface::class, []);
        foreach ($data['rules'] ?? [] as $type => $rule) {
            $this->rules[] = RuleFactory::create($type, $rule);
        }
    }
}
