<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;

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

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/index.blade.php.twig', [
            'entity' => $entity,
            'plural' => $plural,
            'camelCase' => $camelCase,
        ]);

        file_put_contents($viewPath, $view);
    }
}
