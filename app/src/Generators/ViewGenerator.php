<?php

namespace UmigameTech\Catapult\Generators;

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
        $view = <<<EOF
<h1>index of {$entity['name']}</h1>
EOF;

        file_put_contents($viewPath, $view);
    }
}
