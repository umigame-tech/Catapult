<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Datatypes\AttributeType;
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

    public function generateContent($entity)
    {
        $controllerName = self::controllerName($entity);
        $modelName = ModelGenerator::modelName($entity);
        $requestName = RequestGenerator::requestName($entity);

        $inflector = InflectorFactory::create()->build();
        $plural = $inflector->pluralize($entity['name']);

        $authenticatable = $entity['authenticatable'] ?? false;

        $renderer = Renderer::getInstance();
        $data = [
            'controllerName' => $controllerName,
            'modelName' => $modelName,
            'requestName' => $requestName,
            'plural' => $plural,
            'entity' => $entity,
            'authenticatable' => $authenticatable,
        ];

        if ($authenticatable) {
            $loginRequestName = RequestGenerator::loginRequestName($entity);
            $loginKeys = array_values(array_filter(
                $entity['attributes'],
                fn ($attribute) => $attribute['loginKey'] ?? false
            ));
            $loginKeys = array_map(
                fn ($key) => $key['name'],
                $loginKeys
            );

            $password = array_values(array_filter(
                $entity['attributes'],
                fn ($attribute) => $attribute['type'] === AttributeType::Password->value,
            ));
            if (empty($password)) {
                throw new \Exception('Password attribute is not found');
            }
            $data = array_merge($data, [
                'loginRequestName' => $loginRequestName,
                'authName' => AuthGenerator::authName($entity),
                'loginKeys' => $loginKeys,
                'password' => $password[0]['name'],
            ]);
        }

        $controller = $renderer->render('controller.twig', $data);

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

    public function generate()
    {
        foreach ($this->entities as $entity) {
            $result = $this->generateContent($entity);
            if (empty($result)) {
                continue;
            }

            $this->writer->write(...$result);
        }
    }
}
