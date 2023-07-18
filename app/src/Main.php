<?php

namespace UmigameTech\Catapult;

use SplFileObject;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use UmigameTech\Catapult\FileSystem\CopyFileInterface;
use UmigameTech\Catapult\FileSystem\FileCheckerInterface;
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
use UmigameTech\Catapult\FileSystem\FileSystemContainer;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\Generators\AuthGenerator;
use UmigameTech\Catapult\Generators\ModelGenerator;
use UmigameTech\Catapult\Generators\MigrationGenerator;
use UmigameTech\Catapult\Generators\FactoryGenerator;
use UmigameTech\Catapult\Generators\ControllerGenerator;
use UmigameTech\Catapult\Generators\CssSetupGenerator;
use UmigameTech\Catapult\Generators\RequestGenerator;
use UmigameTech\Catapult\Generators\ResourcesSetupGenerator;
use UmigameTech\Catapult\Generators\RouteGenerator;
use UmigameTech\Catapult\Generators\SeederGenerator;
use UmigameTech\Catapult\Generators\TailwindCssSetupGenerator;
use UmigameTech\Catapult\Generators\ViewGenerator;
use UmigameTech\Catapult\ProjectSettings;
use UmigameTech\Catapult\Traits\ProjectPath;

require_once(__DIR__ . '/../vendor/autoload.php');

class Main
{
    use ProjectPath;

    private $projectName = 'project';

    private $targetDir = '/dist';

    private FileReaderInterface $reader;
    private FileWriterInterface $writer;
    private FileRemoverInterface $remover;
    private FileCheckerInterface $checker;
    private CopyFileInterface $copier;

    private $generators = [
        ControllerGenerator::class,
        FactoryGenerator::class,
        MigrationGenerator::class,
        ModelGenerator::class,
        RequestGenerator::class,
        RouteGenerator::class,
        SeederGenerator::class,
        ViewGenerator::class,
    ];

    public function __construct(FileSystemContainer $container = null)
    {
        if (empty($container)) {
            $container = new FileSystemContainer;
        }

        $this->reader = $container->reader;
        $this->writer = $container->writer;
        $this->remover = $container->remover;
        $this->checker = $container->checker;
        $this->copier = $container->copier;
    }

    private function setupEnvFile($projectPath)
    {
        $fileDir = __DIR__ . '/../../app';
        $envFile = preg_replace('/\/$/', '', $fileDir) . '/default.env';
        $this->copier->copyFile(source: $envFile, dest: $projectPath . '/.env');
    }

    private function setupDatabase($projectPath)
    {
        $file = new SplFileObject("{$projectPath}/.env", 'r+');
        $isSqlite = false;
        while ($line = $file->fgets()) {
            if (preg_match('/^DB_CONNECTION=sqlite$/', $line)) {
                $isSqlite = true;
            }
        }

        $settings = ProjectSettings::getInstance();
        if ($isSqlite) {
            $settings->set('db_engine', 'sqlite');

            $sqlitePath = "{$projectPath}/database/database.sqlite";
            touch($sqlitePath);
            $file->fwrite("DB_DATABASE={$sqlitePath}\n");
        }
    }

    public function handle($argv)
    {
        if (empty($argv[1])) {
            echo "Usage: php main.php <path/to/file>\n";
            exit(1);
        }

        if (! $inputFile = $this->reader->read($argv[1])) {
            echo "File not found: {$argv[1]}\n";
            exit(1);
        }

        $schema = Schema::import(json_decode($this->reader->read(__DIR__ . '/JsonSchemas/schema.json')));
        $json = $schema->in(json_decode($inputFile));
        if (!empty($json['project_name'])) {
            $this->projectName = $json['project_name'];
        }
        $projectPath = $this->projectPath();

        $skipInstallation = !empty($argv[2]) && $argv[2] === '--skip-installation';
        if (! $skipInstallation) {
            $composer = new \Composer\Console\Application();
            $composer->setAutoExit(false);
            if ($this->checker->exists($projectPath . '/composer.json')) {
                $composer->run(new StringInput("install --working-dir={$projectPath}"), new ConsoleOutput());
            } else {
                $composer->run(new StringInput("create-project --prefer-dist laravel/laravel {$projectPath}"), new ConsoleOutput());
            }
        }

        $this->setupEnvFile($projectPath);
        $this->setupDatabase($projectPath);

        if (! $skipInstallation) {
            array_unshift($this->generators, TailwindCssSetupGenerator::class);
            array_unshift($this->generators, ResourcesSetupGenerator::class);
            array_unshift($this->generators, CssSetupGenerator::class);
        }

        $authenticatableCount = count(array_filter($json['entities'] ?? [], fn($e) => $e['authenticatable'] ?? false));
        if ($authenticatableCount > 0) {
            array_unshift($this->generators, AuthGenerator::class);
        }

        foreach ($this->generators as $generator) {
            $generator = new $generator($json);
            $generator->generate();
        }
    }
}

(new Main())->handle($argv);
