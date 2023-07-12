<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\FileSystem\FileReader;
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileRemover;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
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

    protected FileRemoverInterface $remover;

    protected array $entities = [];

    public function __construct($json, FileReaderInterface $reader, FileWriterInterface $writer, FileRemoverInterface $remover)
    {
        $this->json = $json;
        $this->projectName = $json['project_name'] ?? 'project';
        $this->prefix = $json['sealed_prefix'] ?? '';
        $this->entities = $json['entities'] ?? [];

        $this->reader = $reader;
        $this->writer = $writer;
        $this->remover = $remover;
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
