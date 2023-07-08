<?php

namespace UmigameTech\Catapult;

use SplFileObject;
use Swaggest\JsonSchema\Schema;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
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
use UmigameTech\Catapult\Traits\ProjectPath;

require_once(__DIR__ . '/../vendor/autoload.php');

class Main
{
    use ProjectPath;

    private $projectName = 'project';

    private $targetDir = '/dist';

    private function setupEnvFile($projectPath)
    {
        $fileDir = __DIR__ . '/../../app';
        $envFile = preg_replace('/\/$/', '', $fileDir) . '/default.env';
        copy($envFile, $projectPath . '/.env');
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

        if ($isSqlite) {
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

        if (! $inputFile = file_get_contents($argv[1])) {
            echo "File not found: {$argv[1]}\n";
            exit(1);
        }

        $schema = Schema::import(json_decode(file_get_contents(__DIR__ . '/JsonSchemas/schema.json')));
        $json = $schema->in(json_decode($inputFile));
        if (!empty($json['project_name'])) {
            $this->projectName = $json['project_name'];
        }
        $projectPath = $this->projectPath();

        $skipInstallation = !empty($argv[2]) && $argv[2] === '--skip-installation';
        if (! $skipInstallation) {
            $composer = new \Composer\Console\Application();
            $composer->setAutoExit(false);
            if (file_exists($projectPath . '/composer.json')) {
                $composer->run(new StringInput("install --working-dir={$projectPath}"), new ConsoleOutput());
            } else {
                $composer->run(new StringInput("create-project --prefer-dist laravel/laravel {$projectPath}"), new ConsoleOutput());
            }
        }

        $this->setupEnvFile($projectPath);
        $this->setupDatabase($projectPath);

        if (! $skipInstallation) {
            $tailwind = new TailwindCssSetupGenerator($json);
            $tailwind->generate();

            $resources = new ResourcesSetupGenerator($json);
            $resources->generate();

            $css = new CssSetupGenerator($json);
            $css->generate();
        }

        $modelGenerator = new ModelGenerator($json);
        $migrationGenerator = new MigrationGenerator($json);
        $factoryGenerator = new FactoryGenerator($json);
        $seederGenerator = new SeederGenerator($json);
        $controllerGenerator = new ControllerGenerator($json);
        $viewGenerator = new ViewGenerator($json);
        $routeGenerator = new RouteGenerator($json);
        $requestGenerator = new RequestGenerator($json);

        foreach ($json['entities'] as $entity) {
            $modelGenerator->generate($entity);
            $migrationGenerator->generate($entity);
            $factoryGenerator->generate($entity);
            $seederGenerator->generate($entity);
            $controllerGenerator->generate($entity);
            $viewGenerator->generate($entity);
            $requestGenerator->generate($entity);
        }

        $routeGenerator->generate($json);
        $seederGenerator->generateDatabaseSeeder($json['entities']);
    }
}

(new Main())->handle($argv);
