<?php

namespace UmigameTech\Catapult\Generators;
use UmigameTech\Catapult\Templates\Renderer;

class SeederGenerator extends Generator
{

    public function generateDatabaseSeeder($entities)
    {
        $seeders = array_map(
            function ($entity) {
                return ModelGenerator::modelName($entity) . 'Seeder';
            },
            $entities
        );

        $renderer = Renderer::getInstance();
        $seeder = $renderer->render('seeders/database.twig', [
            'seeders' => $seeders,
        ]);

        $projectPath = $this->projectPath();
        $seederPath = "{$projectPath}" . '/database/seeders/DatabaseSeeder.php';
        unlink($seederPath);
        file_put_contents($seederPath, $seeder);
    }

    public function generate($entity)
    {
        $modelName = ModelGenerator::modelName($entity);
        $seederName = $modelName . 'Seeder';

        $renderer = Renderer::getInstance();
        $seeder = $renderer->render('seeders/seeder.twig', [
            'modelName' => $modelName,
            'seederName' => $seederName,
            'entity' => $entity,
        ]);

        $projectPath = $this->projectPath();
        $seederPath = "{$projectPath}" . '/database/seeders/' . $seederName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($seederPath)) {
            unlink($seederPath);
        }

        file_put_contents($seederPath, $seeder);
    }
}
