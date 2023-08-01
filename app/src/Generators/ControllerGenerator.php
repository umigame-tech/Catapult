<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Attribute;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ControllerGenerator extends Generator
{
    const METHOD_GET = 'get';
    const METHOD_PUT = 'put';
    const METHOD_POST = 'post';
    const METHOD_PATCH = 'patch';
    const METHOD_DELETE = 'delete';

    public static $actions = [
        'index' => [
            'method' => self::METHOD_GET,
            'route' => '',
        ],
        'create' => [
            'method' => self::METHOD_GET,
            'route' => 'create',
        ],
        'show' => [
            'method' => self::METHOD_GET,
            'route' => '{id}',
        ],
        'storeConfirm' => [
            'method' => self::METHOD_POST,
            'route' => 'storeConfirm',
        ],
        'store' => [
            'method' => self::METHOD_POST,
            'route' => '',
        ],
        'edit' => [
            'method' => self::METHOD_GET,
            'route' => '{id}/edit',
        ],
        'updateConfirm' => [
            'method' => self::METHOD_POST,
            'route' => '{id}/updateConfirm',
        ],
        'update' => [
            'method' => [self::METHOD_PUT, self::METHOD_PATCH],
            'route' => '{id}',
        ],
        'destroyConfirm' => [
            'method' => self::METHOD_GET,
            'route' => '{id}/destroyConfirm',
        ],
        'destroy' => [
            'method' => self::METHOD_DELETE,
            'route' => '{id}',
        ],
    ];

    public function generateContent(Entity $entity)
    {
        $controllerName = $entity->controllerName();
        $modelName = $entity->modelName();
        $requestName = $entity->requestName();

        $plural = $this->inflector->pluralize($entity->name);

        $authenticatable = $entity->isAuthenticatable();

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
            $loginRequestName = $entity->loginRequestName();
            $loginKeys = $entity->attributes->filter(
                fn (Attribute $attribute) => $attribute->loginKey
            );
            $loginKeys = $loginKeys->map(
                fn (Attribute $key) => $key->name,
            );

            $password = $entity->attributes->filter(
                fn (Attribute $attribute) => $attribute->type === AttributeType::Password
            );
            if (empty($password)) {
                throw new \Exception('Password attribute is not found');
            }
            $data = array_merge($data, [
                'loginRequestName' => $loginRequestName,
                'authName' => $entity->authName(),
                'loginKeys' => $loginKeys,
                'password' => $password[0]->name,
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

    public function generateDashboardContent(Entity $entity)
    {
        $controllerName = $entity->dashboardControllerName();
        $controllerPath = "{$this->projectPath()}/app/Http/Controllers{$controllerName}.php";

        return [
            'path' => $controllerPath,
            'content' => '',
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
