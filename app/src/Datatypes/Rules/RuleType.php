<?php

namespace UmigameTech\Catapult\Datatypes\Rules;

enum RuleType: string
{
    case Required = 'required';
    case Nullable = 'nullable';
    case Min = 'min';
    case Max = 'max';
}
