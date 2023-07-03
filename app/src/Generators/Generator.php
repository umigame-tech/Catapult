<?php

namespace UmigameTech\Catapult\Generators;

abstract class Generator
{
    const INDENT = '    ';

    protected $targetDir = '/dist';

    protected $projectName = 'project';

    public function __construct($projectName) {
        $this->projectName = $projectName;
    }

    protected function indents(int $level): string
    {
        return str_repeat(self::INDENT, $level);
    }
}
