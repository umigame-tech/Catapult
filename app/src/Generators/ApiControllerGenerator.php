<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ApiControllerGenerator extends ControllerGenerator
{
    public static $apiActions = [
        'index' => [
            'method' => self::METHOD_GET,
            'params' => [],
        ],
        'show' => [
            'method' => self::METHOD_GET,
            'params' => ['id'],
        ],
        'create' => [
            'method' => self::METHOD_PUT,
            'params' => [],
        ],
        'update' => [
            'method' => self::METHOD_PATCH,
            'params' => ['id'],
        ],
        'destroy' => [
            'method' => self::METHOD_DELETE,
            'params' => ['id'],
        ],
    ];

    public function generateContent(Entity $entity)
    {
        $controllerName = $entity->apiControllerName();
        $modelName = $entity->modelName();
        $requestName = $entity->apiRequestName();

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
            // TODO: ログイン可能エンティティの場合の処理
        }

        $controller = $renderer->render('api/controller', $data);

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
