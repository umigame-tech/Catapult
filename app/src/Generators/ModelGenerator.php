<?php

namespace UmigameTech\Catapult\Generators;
use UmigameTech\Catapult\Templates\Renderer;

class ModelGenerator extends Generator
{
    static public function modelName($entity)
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        ));
    }

    public function generate($entity) {
        $modelName = self::modelName($entity);

        $fillableList = array_map(
            fn ($attribute) => $attribute['name'],
            $entity['attributes']
        );

        $renderer = Renderer::getInstance();
        $model = $renderer->render('model.twig', [
            'modelName' => $modelName,
            'fillableList' => $fillableList,
        ]);

        $projectPath = $this->projectPath();
        $modelPath = "{$projectPath}" . '/app/Models/' . $modelName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($modelPath)) {
            unlink($modelPath);
        }

        file_put_contents($modelPath, $model);
    }

}
