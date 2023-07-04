<?php

namespace UmigameTech\Catapult;

use SplFileObject;
use UmigameTech\Catapult\Generators\ModelGenerator;
use UmigameTech\Catapult\Generators\MigrationGenerator;
use UmigameTech\Catapult\Generators\FactoryGenerator;
use UmigameTech\Catapult\Generators\ControllerGenerator;
use UmigameTech\Catapult\Generators\RouteGenerator;
use UmigameTech\Catapult\Generators\SeederGenerator;
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

        $json = json_decode($inputFile, true);
        if (!empty($json['project_name'])) {
            $this->projectName = $json['project_name'];
        }
        $projectPath = $this->projectPath();

        $skipInstallation = !empty($argv[2]) && $argv[2] === '--skip-installation';
        if (! $skipInstallation) {
            if (file_exists($projectPath . '/composer.json')) {
                exec("composer install --working-dir={$projectPath}");
            } else {
                exec("composer create-project --prefer-dist laravel/laravel {$projectPath}");
            }
        }

        $this->setupEnvFile($projectPath);
        $this->setupDatabase($projectPath);

        $modelGenerator = new ModelGenerator($this->projectName);
        $migrationGenerator = new MigrationGenerator($this->projectName);
        $factoryGenerator = new FactoryGenerator($this->projectName);
        $seederGenerator = new SeederGenerator($this->projectName);
        $controllerGenerator = new ControllerGenerator($this->projectName);
        $viewGenerator = new ViewGenerator($this->projectName);
        $routeGenerator = new RouteGenerator($this->projectName);

        $routeGenerator->refreshRoutes();

        $prefix = $json['sealed_prefix'] ?? "";
        $indent = empty($prefix) ? 0 : 1;
        $routeGenerator->sealedRoutesOpen($prefix);

        foreach ($json['entities'] as $entity) {
            $modelGenerator->generate($entity);
            $migrationGenerator->generate($entity);
            $factoryGenerator->generate($entity);
            $seederGenerator->generate($entity);
            $controllerGenerator->generate($entity);
            $viewGenerator->generate($entity);
            $routeGenerator->generate($entity, $indent);
        }

        $seederGenerator->generateDatabaseSeeder($json['entities']);
        $routeGenerator->sealedRoutesClose($prefix);
    }
}

(new Main())->handle($argv);
