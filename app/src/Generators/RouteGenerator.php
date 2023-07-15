<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;

class RouteGenerator extends Generator
{
    public function __construct(...$args)
    {
        parent::__construct(...$args);
    }

    private function convertActionName($entity, $prefix)
    {
        $inflector = InflectorFactory::create()->build();
        $converted = [];
        $actions = ControllerGenerator::$actions;
        foreach ($actions as $actionName => $action) {
            $spaces = $prefix ? '    ' : '';

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

        $prefix = $this->prefix;
        $entities = array_map(
            function ($entity) use ($prefix) {
                $entity['controllerName'] = ControllerGenerator::controllerName($entity);
                $routes = $this->convertActionName($entity, $prefix);
                if ($entity['authenticatable'] ?? false) {
                    $controllerName = $entity['controllerName'];
                    $routes['login'] = "    Route::get('{$entity['name']}/login', [{$controllerName}::class, 'login'])->name('{$entity['name']}.login');";
                    $routes['loginSubmit'] = "    Route::post('{$entity['name']}/login', [{$controllerName}::class, 'loginSubmit'])->name('{$entity['name']}.loginSubmit');";
                    $routes['logout'] = "    Route::post('{$entity['name']}/logout', [{$controllerName}::class, 'logout'])->name('{$entity['name']}.logout');";
                }

                $entity['routes'] = $routes;
                return $entity;
            },
            $this->entities
        );

        $routes = $renderer->render('routes/web.php.twig', [
            'prefix' => $prefix,
            'entities' => $entities,
        ]);

        return [
            'path' => $webRoutePath,
            'content' => $routes,
        ];
    }

    public function generate()
    {
        $content = $this->generateContent();
        $this->writer->write(...$content);
    }
}
