<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class SeederGenerator extends Generator
{

    public function generateDatabaseSeeder()
    {
        $entities = $this->entities;
        $seeders = $entities->map(
            function (Entity $entity) {
                return ModelGenerator::modelName($entity) . 'Seeder';
            }
        );

        $renderer = Renderer::getInstance();
        $seeder = $renderer->render('seeders/database.twig', [
            'seeders' => $seeders,
        ]);

        $projectPath = $this->projectPath();
        $seederPath = "{$projectPath}" . '/database/seeders/DatabaseSeeder.php';
        if ($this->checker->exists($seederPath)) {
            $this->remover->remove($seederPath);
        }

        return [
            'path' => $seederPath,
            'content' => $seeder,
        ];
    }

    public function generateContent(Entity $entity)
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
        if ($this->checker->exists($seederPath)) {
            $this->remover->remove($seederPath);
        }

        return [
            'path' => $seederPath,
            'content' => $seeder,
        ];
    }

    public function generate()
    {
        foreach ($this->entities as $entity) {
            $generated = $this->generateContent($entity);
            if (empty($generated)) {
                continue;
            }

            $this->writer->write(...$generated);
        }

        $generated = $this->generateDatabaseSeeder();
        $this->writer->write(...$generated);
    }
}
