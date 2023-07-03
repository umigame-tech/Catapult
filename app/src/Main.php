<?php

namespace UmigameTech\Catapult;

use UmigameTech\Catapult\Generators\ModelGenerator;
use UmigameTech\Catapult\Generators\MigrationGenerator;
use UmigameTech\Catapult\Generators\FactoryGenerator;
use UmigameTech\Catapult\Generators\ControllerGenerator;
use UmigameTech\Catapult\Generators\RouteGenerator;
use UmigameTech\Catapult\Generators\ViewGenerator;

require_once(__DIR__ . '/../vendor/autoload.php');

class Main
{
    private $projectName = 'project';

    private $targetDir = '/dist';

    private function setupEnvFile()
    {
        $fileDir = __DIR__ . '/../../app';
        $envFile = preg_replace('/\/$/', '', $fileDir) . '/default.env';
        copy($envFile, $this->targetDir . '/.env');
    }

    private function setupDatabase()
    {
        touch($this->targetDir . '/database/database.sqlite');
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
        $this->targetDir .= '/' . $this->projectName;

        $skipInstallation = !empty($argv[2]) && $argv[2] === '--skip-installation';
        if (! $skipInstallation) {
            if (file_exists($this->targetDir . '/composer.json')) {
                exec("composer install --working-dir={$this->targetDir}");
            } else {
                exec("composer create-project --prefer-dist laravel/laravel {$this->targetDir}");
            }
        }

        $this->setupEnvFile();
        $this->setupDatabase();

        $modelGenerator = new ModelGenerator($this->projectName);
        $migrationGenerator = new MigrationGenerator($this->projectName);
        $factoryGenerator = new FactoryGenerator($this->projectName);
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
            $controllerGenerator->generate($entity);
            $viewGenerator->generate($entity);
            $routeGenerator->generate($entity, $indent);
        }

        $routeGenerator->sealedRoutesClose($prefix);
    }
}

(new Main())->handle($argv);
