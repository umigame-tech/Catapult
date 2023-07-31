<?php

namespace UmigameTech\Catapult\Datatypes;

use UmigameTech\Catapult\Datatypes\Rules\RuleFactory;
use UmigameTech\Catapult\Datatypes\Rules\RuleInterface;
use Newnakashima\TypedArray\TypedArray;

class Attribute
{
    public string $name = '';
    public AttributeType $type;
    public bool $loginKey = false;
    /** @var RuleInterface[] */
    public TypedArray $rules;

    /** for html form */
    public string $inputType = '';
    public string $inputName = '';

    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->loginKey = $data['loginKey'] ?? false;

        $this->type = AttributeType::from($data['type']);

        $this->rules = new TypedArray(RuleInterface::class, []);
        foreach ($data['rules'] ?? [] as $type => $rule) {
            $this->rules[] = RuleFactory::create($type, $rule);
        }
    }
}
