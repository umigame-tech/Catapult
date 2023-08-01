<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\ProjectSettings;
use UmigameTech\Catapult\Templates\Renderer;

class InitialDataSeederGenerator extends Generator
{
    private function loadInitialData(string $path): mixed
    {
        $settings = ProjectSettings::getInstance();
        $dirPath = $settings->get('input_dir');

        $data = $this->reader->read("{$dirPath}/{$path}");
        $json = json_decode($data, true);
        return $json;
    }

    public function generateContent(Entity $entity)
    {
        $initialData = $this->loadInitialData($entity->dataPath);

        $seederPath = $this->projectPath() . "/database/seeders/{$entity->initialDataSeederName()}.php";
        $renderer = Renderer::getInstance();
        $seeder = $renderer->render('seeders/initialDataSeeder.twig', [
            'entity' => $entity,
            'seederName' => $entity->initialDataSeederName(),
            'modelName' => $entity->modelName(),
            'initialData' => $initialData,
        ]);

        return [
            'path' => $seederPath,
            'content' => $seeder,
        ];
    }

    public function generate()
    {
        foreach ($this->entities as $entity) {
            if (! $entity->hasInitialData()) {
                continue;
            }

            $content = $this->generateContent($entity);

            $this->writer->write(...$content);
        }
    }
}
