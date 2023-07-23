<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ModelGenerator extends Generator
{
    static public function modelName(Entity $entity)
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity->name)
        ));
    }

    public function generateContent($entity) {
        $modelName = self::modelName($entity);
        $authenticatable = $entity['authenticatable'] ?? false;
        $parentClass = $authenticatable ? 'Authenticatable' : 'Model';
        $parentClassImport = $authenticatable
            ? 'use Illuminate\Foundation\Auth\User as Authenticatable;'
            : 'use Illuminate\Database\Eloquent\Model;';

        $fillableList = array_map(
            fn ($attribute) => $attribute['name'],
            $entity['attributes']
        );

        $hiddenList = array_filter(
            $entity['attributes'],
            fn ($attribute) => $attribute['type'] === AttributeType::Password->value
        );

        $renderer = Renderer::getInstance();
        $model = $renderer->render('model.twig', [
            'modelName' => $modelName,
            'fillableList' => $fillableList,
            'hiddenList' => $hiddenList,
            'parentClass' => $parentClass,
            'parentClassImport' => $parentClassImport,
        ]);

        $projectPath = $this->projectPath();
        $modelPath = "{$projectPath}" . '/app/Models/' . $modelName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($modelPath)) {
            unlink($modelPath);
        }

        return [
            'path' => $modelPath,
            'content' => $model,
        ];
    }

    public function generate()
    {
        foreach ($this->entities as $entity) {
            $content = $this->generateContent($entity);
            if (empty($content)) {
                continue;
            }

            $this->writer->write(...$content);
        }
    }
}
