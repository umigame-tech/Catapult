<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use Newnakashima\TypedArray\TypedArray;
use UmigameTech\Catapult\Datatypes\Project;
use UmigameTech\Catapult\FileSystem\CopyFileInterface;
use UmigameTech\Catapult\FileSystem\FileCheckerInterface;
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
use UmigameTech\Catapult\FileSystem\FileSystemContainer;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\FileSystem\MakeDirectoryInterface;
use UmigameTech\Catapult\Traits\ProjectPath;

abstract class Generator
{
    use ProjectPath;

    const INDENT = '    ';

    protected Project $project;

    protected $targetDir = '/dist';

    protected $projectName = 'project';

    protected FileReaderInterface $reader;

    protected FileWriterInterface $writer;

    protected FileRemoverInterface $remover;

    protected FileCheckerInterface $checker;

    protected CopyFileInterface $copier;

    protected TypedArray $entities;

    protected $inflector;

    protected $makeDirectory;

    public function __construct(Project $project, FileSystemContainer $container = null)
    {
        $this->project = $project;
        $this->projectName = $this->project->projectName;
        $this->entities = $this->project->entities;

        if (empty($container)) {
            $container = new FileSystemContainer;
        }

        $this->reader = $container->reader;
        $this->writer = $container->writer;
        $this->remover = $container->remover;
        $this->checker = $container->checker;
        $this->copier = $container->copier;
        $this->makeDirectory = $container->makeDirectory;

        $this->inflector = InflectorFactory::create()->build();
    }

    protected function indents(int $level): string
    {
        return str_repeat(self::INDENT, $level);
    }

    protected function baseUri($entity)
    {
        if (! ($entity['authenticatable'] ?? false)) {
            return '/' . $entity['name'];
        }

        $prefix = $this->inflector->pluralize($entity['name']);
        return '/' . (!empty($prefix) ? "{$prefix}/" : '') . $entity['name'];
    }

    public function setMakeDirectory(MakeDirectoryInterface $makeDirectory)
    {
        $this->makeDirectory = $makeDirectory;
    }
}
