<?php

namespace UmigameTech\Catapult\Generators;

class ControllerGenerator extends Generator
{
    public static function controllerName($entity)
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        )) . 'Controller';
    }

    public function generate($entity)
    {
        $controllerName = self::controllerName($entity);

        $controller = <<<EOF
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class {$controllerName} extends Controller
{
    public function index()
    {
        return view('{$entity['name']}.index');
    }

    public function show() { }
    public function new() { }
    public function createConfirm() { }
    public function create() { }
    public function edit() { }
    public function editConfirm() { }
    public function update() { }
    public function deleteConfirm() { }
    public function delete() { }
}

EOF;

        $projectPath = $this->projectPath();
        $controllerPath = "{$projectPath}/app/Http/Controllers/{$controllerName}.php";
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($controllerPath)) {
            unlink($controllerPath);
        }

        file_put_contents($controllerPath, $controller);
    }
}
