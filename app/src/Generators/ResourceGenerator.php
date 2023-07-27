<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ResourceGenerator extends Generator
{
    public function generateContent(Entity $entity)
    {
        $projectPath = $this->projectPath();
        $resourceName = $entity->resourceName();
        $resourcePath = $projectPath . '/app/Http/Resources/' . $resourceName . '.php';

        $renderer = Renderer::getInstance();
        $resource = $renderer->render('resource.twig', [
            'resourceName' => $resourceName,
        ]);

        return [
            'path' => $resourcePath,
            'content' => $resource,
        ];
    }

    public function generateResourceCollection(Entity $entity)
    {
        $projectPath = $this->projectPath();
        $resourceCollectionName = $entity->resourceCollectionName();
        $resourcePath = $projectPath . '/app/Http/Resources/' . $resourceCollectionName . '.php';

        $renderer = Renderer::getInstance();
        $resource = $renderer->render('resourceCollection.twig', [
            'resourceCollectionName' => $resourceCollectionName,
        ]);

        return [
            'path' => $resourcePath,
            'content' => $resource,
        ];
    }

    public function generate()
    {
        foreach ($this->entities as $entity) {
            $result = $this->generateContent($entity);
            $collection = $this->generateResourceCollection($entity);
            if (empty($result) || empty($collection)) {
                continue;
            }

            $this->writer->write(...$result);
            $this->writer->write(...$collection);
        }
    }
}
