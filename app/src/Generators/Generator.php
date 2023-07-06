<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Traits\ProjectPath;

abstract class Generator
{
    use ProjectPath;

    const INDENT = '    ';

    protected $targetDir = '/dist';

    protected $projectName = 'project';

    protected $prefix = '';

    public function __construct($json) {
        $this->projectName = $json['project_name'] ?? 'project';
        $this->prefix = $json['sealed_prefix'] ?? '';
    }

    protected function indents(int $level): string
    {
        return str_repeat(self::INDENT, $level);
    }

    protected function baseUri($entity)
    {
        return '/' . (!empty($this->prefix) ? "{$this->prefix}/" : '') . $entity['name'];
    }
}
