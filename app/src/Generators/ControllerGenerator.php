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
        'index' => [
            'method' => self::METHOD_GET,
            'params' => [],
        ],
        'show' => [
            'method' => self::METHOD_GET,
            'params' => ['id'],
        ],
        'new' => [
            'method' => self::METHOD_GET,
            'params' => [],
        ],
        'createConfirm' => [
            'method' => self::METHOD_POST,
            'params' => [],
        ],
        'create' => [
            'method' => self::METHOD_POST,
            'params' => [],
        ],
        'edit' => [
            'method' => self::METHOD_GET,
            'params' => ['id'],
        ],
        'updateConfirm' => [
            'method' => self::METHOD_POST,
            'params' => ['id'],
        ],
        'update' => [
            'method' => self::METHOD_POST,
            'params' => ['id'],
        ],
        'destroyConfirm' => [
            'method' => self::METHOD_GET,
            'params' => ['id'],
        ],
        'destroy' => [
            // HTMLフォームからの送信だとDELETEメソッドは使えないので
            'method' => self::METHOD_POST,
            'params' => ['id'],
        ],
    ];

    public function generate($entity)
    {
        $controllerName = self::controllerName($entity);
        $modelName = ModelGenerator::modelName($entity);
        $requestName = RequestGenerator::requestName($entity);

        $inflector = InflectorFactory::create()->build();
        $plural = $inflector->pluralize($entity['name']);

        $renderer = Renderer::getInstance();
        $controller = $renderer->render('controller.twig', [
            'controllerName' => $controllerName,
            'modelName' => $modelName,
            'requestName' => $requestName,
            'plural' => $plural,
            'entity' => $entity,
        ]);

        $projectPath = $this->projectPath();
        $controllerPath = "{$projectPath}/app/Http/Controllers/{$controllerName}.php";
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($controllerPath)) {
            unlink($controllerPath);
        }

        return [
            'path' => $controllerPath,
            'content' => $controller,
        ];
    }
}
