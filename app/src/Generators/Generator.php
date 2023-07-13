<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\FileSystem\CopyFileInterface;
use UmigameTech\Catapult\FileSystem\FileCheckerInterface;
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
use UmigameTech\Catapult\FileSystem\FileSystemContainer;
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

    protected FileCheckerInterface $checker;

    protected CopyFileInterface $copier;

    protected array $entities = [];

    public function __construct($json, $container = null)
    {
        $this->json = $json;
        $this->projectName = $json['project_name'] ?? 'project';
        $this->prefix = $json['sealed_prefix'] ?? '';
        $this->entities = $json['entities'] ?? [];

        if (empty($container)) {
            $container = new FileSystemContainer;
        }

        $this->reader = $container->reader;
        $this->writer = $container->writer;
        $this->remover = $container->remover;
        $this->checker = $container->checker;
        $this->copier = $container->copier;
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
