<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ModelGenerator extends Generator
{
    public function generateContent(Entity $entity) {
        $modelName = $entity->modelName();
        $authenticatable = $entity->isAuthenticatable();
        $parentClass = $authenticatable ? 'Authenticatable' : 'Model';
        $parentClassImport = $authenticatable
            ? 'use Illuminate\Foundation\Auth\User as Authenticatable;'
            : 'use Illuminate\Database\Eloquent\Model;';

        $fillableList = $entity->attributes->map(
            fn ($attribute) => $attribute->name,
        );

        $hiddenList = $entity->attributes->filter(
            fn ($attribute) => $attribute->type === AttributeType::Password,
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
