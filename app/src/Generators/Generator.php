<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\FileSystem\FileReader;
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileWriter;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\Traits\ProjectPath;

abstract class Generator
{
    use ProjectPath;

    const INDENT = '    ';

    protected $json = [];

    protected $targetDir = '/dist';

    protected $projectName = 'project';

    protected $prefix = '';

    protected FileReaderInterface $reader;

    protected FileWriterInterface $writer;

    protected array $entities = [];

    public function __construct($json, FileReaderInterface $reader = new FileReader(), FileWriterInterface $writer = new FileWriter()) {
        $this->json = $json;
        $this->projectName = $json['project_name'] ?? 'project';
        $this->prefix = $json['sealed_prefix'] ?? '';
        $this->entities = $json['entities'] ?? [];

        $this->reader = $reader;
        $this->writer = $writer;
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
