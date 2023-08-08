<?php

namespace UmigameTech\Catapult\Datatypes;

class BelongsTo
{
    public string $name = '';
    public string $type = '';

    public function __construct($data) {
        $this->name = $data['name'];
        $this->type = $data['type'];
    }
}
