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

    protected $targetDir = '/dist';

    protected $projectName = 'project';

    protected $prefix = '';

    protected FileReaderInterface $reader;

    protected FileWriterInterface $writer;

    public function __construct($json, FileReaderInterface $reader = new FileReader(), FileWriterInterface $writer = new FileWriter()) {
        $this->projectName = $json['project_name'] ?? 'project';
        $this->prefix = $json['sealed_prefix'] ?? '';

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

    public function writeToFile($path, $content)
    {
        $this->writer->write($path, $content);
    }
}
