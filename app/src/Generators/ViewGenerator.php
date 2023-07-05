<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;

class ViewGenerator extends Generator
{
    private $projectPath = '';
    // view CRUD用のBladeテンプレート
    public function generate($entity)
    {
        $projectPath = $this->projectPath();
        $this->projectPath = $projectPath;

        // 前回のディレクトリが残っている場合は削除する
        if (file_exists($this->projectPath . '/resources/views/' . $entity['name'])) {
            exec("rm -rf {$this->projectPath}/resources/views/{$entity['name']}");
        }

        mkdir($this->projectPath . '/resources/views/' . $entity['name'], 0755, true);

        $this->generateIndexView($entity);
        $this->generateShowView($entity);
    }

    private function generateIndexView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/index.blade.php';

        $modelName = ModelGenerator::modelName($entity);
        $camelCase = lcfirst($modelName);

        $inflector = InflectorFactory::create()->build();
        $plural = $inflector->pluralize($entity['name']);

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/index.blade.php.twig', [
            'entity' => $entity,
            'plural' => $plural,
            'camelCase' => $camelCase,
        ]);

        file_put_contents($viewPath, $view);
    }

    private function generateShowView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/show.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/show.blade.php.twig', [
            'entity' => $entity,
        ]);

        file_put_contents($viewPath, $view);
    }
}
