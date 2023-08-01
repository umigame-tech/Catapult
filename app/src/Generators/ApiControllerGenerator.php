<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ApiControllerGenerator extends ControllerGenerator
{
    public static $apiActions = [
        'index' => [
            'method' => self::METHOD_GET,
            'route' => '',
        ],
        'show' => [
            'method' => self::METHOD_GET,
            'route' => '{id}',
        ],
        'store' => [
            'method' => self::METHOD_POST,
            'route' => '',
        ],
        'update' => [
            'method' => [self::METHOD_PATCH, self::METHOD_PUT],
            'route' => '{id}',
        ],
        'destroy' => [
            'method' => self::METHOD_DELETE,
            'route' => '{id}',
        ],
    ];

    public function generateContent(Entity $entity)
    {
        $controllerName = $entity->apiControllerName();
        $modelName = $entity->modelName();
        $storeRequestName = $entity->apiStoreRequestName();
        $updateRequestName = $entity->apiUpdateRequestName();
        $resourceName = $entity->resourceName();
        $resourceCollectionName = $entity->resourceCollectionName();

        $plural = $this->inflector->pluralize($entity->name);

        $authenticatable = $entity->isAuthenticatable();

        $renderer = Renderer::getInstance();
        $data = [
            'controllerName' => $controllerName,
            'modelName' => $modelName,
            'storeRequestName' => $storeRequestName,
            'updateRequestName' => $updateRequestName,
            'plural' => $plural,
            'entity' => $entity,
            'authenticatable' => $authenticatable,
            'resourceName' => $resourceName,
            'resourceCollectionName' => $resourceCollectionName,
        ];

        if ($authenticatable) {
            // TODO: ログイン可能エンティティの場合の処理
        }

        $controller = $renderer->render('api/controller.twig', $data);

        $projectPath = $this->projectPath();
        $controllerPath = "{$projectPath}/app/Http/Controllers/Api/{$controllerName}.php";
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
