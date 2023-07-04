<?php

namespace UmigameTech\Catapult\Datatypes;

enum AttributeType: string
{
    case String = 'string';
    case Username = 'username';
    case Email = 'email';
    case Tel = 'tel';
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Date = 'date';
    case Datetime = 'datetime';
    case Time = 'time';
    case Decimal = 'decimal';
    case Text = 'text';
}