<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;

class RouteGenerator extends Generator
{
    const FOR_EVERYONE = ['all', 'everyone', 'any', '*'];

    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    private function convertActionName($entity, $indentLevel)
    {
        $inflector = InflectorFactory::create()->build();
        $converted = [];
        $actions = ControllerGenerator::$actions;
        foreach ($actions as $actionName => $action) {
            $spaces = $this->indents($indentLevel);

            $entityPath = $entity['name'];
            $actionPath = '/' . $actionName;
            if ($actionName === 'index') {
                $entityPath = $inflector->pluralize($entity['name']);
                $actionPath = '';
            }

            $params = implode('/', array_map(
                fn ($p) => '{' . $p . '}',
                $action['params'])
            );
            $params = $params ? '/' . $params : '';

            $converted[$actionName] = "{$spaces}Route::{$action['method']}('{$entityPath}{$actionPath}{$params}', "
                . "[{$entity['controllerName']}::class, '{$actionName}'])->name('{$entity['name']}.{$actionName}');";
        }

        return $converted;
    }

    public function generateContent()
    {
        $renderer = Renderer::getInstance();
        $webRoutePath = $this->projectPath() . '/routes/web.php';

        $authList = $this->makeAuthList();

        $forEveryone = $this->makeForEveryone();

        $routes = $renderer->render('routes/web.php.twig', [
            'authList' => $authList,
            'forEveryone' => $forEveryone,
            'entities' => $this->entities,
        ]);

        return [
            'path' => $webRoutePath,
            'content' => $routes,
        ];
    }

    private function makeAuthList()
    {
        $authNames = array_values(array_map(
            fn ($entity) => $this->inflector->pluralize($entity['name']),
            array_filter(
                $this->entities,
                fn ($entity) => $entity['authenticatable'] ?? false
            )
        ));
        $authList = [];
        foreach ($authNames as $authName) {
            $filtered = array_filter(
                $this->entities,
                function ($entity) use ($authName) {
                    $allowedFor = array_map(
                        fn ($allowed) => $this->inflector->pluralize($allowed),
                        $entity['allowedFor'] ?? []
                    );
                    return in_array($authName, $allowedFor);
                }
            );

            $authList[$authName] = $this->convertEntitiesForRoute($filtered, 1);
        }

        return $authList;
    }

    private function makeForEveryone()
    {
        $filtered = array_filter(
            $this->entities,
            fn ($entity) => in_array($entity['allowedFor'] ?? [], self::FOR_EVERYONE)
        );

        return $this->convertEntitiesForRoute($filtered);
    }

    private function convertEntitiesForRoute($entities, $indentLevel = 0)
    {
        $entities = array_map(
            function ($entity) use ($indentLevel) {
                $entity['controllerName'] = ControllerGenerator::controllerName($entity);
                $routes = $this->convertActionName($entity, $indentLevel);
                if ($entity['authenticatable'] ?? false) {
                    $controllerName = $entity['controllerName'];
                    $routes['login'] = $this->indents($indentLevel) . "Route::get('{$entity['name']}/login', [{$controllerName}::class, 'login'])->name('{$entity['name']}.login');";
                    $routes['loginSubmit'] = $this->indents($indentLevel) . "Route::post('{$entity['name']}/login', [{$controllerName}::class, 'loginSubmit'])->name('{$entity['name']}.loginSubmit');";
                    $routes['logout'] = $this->indents($indentLevel) . "Route::post('{$entity['name']}/logout', [{$controllerName}::class, 'logout'])->name('{$entity['name']}.logout');";
                }

                $entity['routes'] = $routes;
                return $entity;
            },
            $this->entities
        );

        return $entities;
    }

    public function generate()
    {
        $content = $this->generateContent();
        $this->writer->write(...$content);
    }
}
