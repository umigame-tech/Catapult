<?php

namespace UmigameTech\Catapult\Datatypes;

enum AttributeType: string
{
    case Select = 'select';
    case Radio = 'radio';
    case Multiple = 'multiple';
    case String = 'string';
    case Username = 'username';
    case Email = 'email';
    case Password = 'password';
    case Tel = 'tel';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Date = 'date';
    case Datetime = 'datetime';
    case Time = 'time';
    case Decimal = 'decimal';
    case Text = 'text';
}
