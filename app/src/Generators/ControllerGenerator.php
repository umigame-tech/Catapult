<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;

class ControllerGenerator extends Generator
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';

    public static function controllerName($entity)
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        )) . 'Controller';
    }

    public static $actions = [
        'index' => self::METHOD_GET,
        'show' => self::METHOD_GET,
        'new' => self::METHOD_GET,
        'createConfirm' => self::METHOD_POST,
        'create' => self::METHOD_POST,
        'edit' => self::METHOD_GET,
        'editConfirm' => self::METHOD_POST,
        'update' => self::METHOD_POST,
        'destroyConfirm' => self::METHOD_GET,
        'destroy' => self::METHOD_POST, // HTMLフォームからの送信だとDELETEメソッドは使えないので
    ];

    public function generate($entity)
    {
        $controllerName = self::controllerName($entity);
        $modelName = ModelGenerator::modelName($entity);

        $inflector = InflectorFactory::create()->build();
        $plural = $inflector->pluralize($entity['name']);

        $renderer = Renderer::getInstance();
        $controller = $renderer->render('controllers/index.twig', [
            'controllerName' => $controllerName,
            'modelName' => $modelName,
            'plural' => $plural,
            'entity' => $entity,
        ]);

        $projectPath = $this->projectPath();
        $controllerPath = "{$projectPath}/app/Http/Controllers/{$controllerName}.php";
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($controllerPath)) {
            unlink($controllerPath);
        }

        file_put_contents($controllerPath, $controller);
    }
}
