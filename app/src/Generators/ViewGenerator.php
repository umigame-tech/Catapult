<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;

class ViewGenerator extends Generator
{

    // view CRUD用のBladeテンプレート
    public function generate($entity)
    {
        $this->generateIndexView($entity);
        
        // TODO: index以外のビューテンプレート生成
    }

    private function generateIndexView($entity)
    {
        $projectPath = $this->projectPath();
        // 前回のディレクトリが残っている場合は削除する
        if (file_exists($projectPath . '/resources/views/' . $entity['name'])) {
            exec("rm -rf {$projectPath}/resources/views/{$entity['name']}");
        }

        mkdir($projectPath . '/resources/views/' . $entity['name'], 0755, true);
        $viewPath = $projectPath . '/resources/views/' . $entity['name'] . '/index.blade.php';

        $modelName = ModelGenerator::modelName($entity);
        $camelCase = lcfirst($modelName);

        $inflector = InflectorFactory::create()->build();
        $plural = $inflector->pluralize($entity['name']);

        $attributeHeaders = implode(
            "\n" . $this->indents(3),
            array_map(
                fn ($attribute) => "<li>{$attribute['name']}</li>",
                $entity['attributes']
            )
        );

        $attributes = implode(
            "\n" . $this->indents(3),
            array_map(
                fn ($attribute) => "<li>{{ \${$camelCase}->{$attribute['name']} }}</li>",
                $entity['attributes']
            )
        );

        $view = <<<EOF
@extends('layouts.app')
<h1 class="mb-8">index of {$entity['name']}</h1>
<ul>
    <li>
        <ul>
            {$attributeHeaders}
        </ul>
    </li>
@foreach (\${$plural} as \${$camelCase})
    <li>
        <ul>
            {$attributes}
        </ul>
    </li>
@endforeach
</ul>
EOF;

        file_put_contents($viewPath, $view);
    }
}
