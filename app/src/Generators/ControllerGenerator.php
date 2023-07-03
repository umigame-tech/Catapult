<?php

namespace UmigameTech\Catapult\Generators;

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
        'index' => self::METHOD_GET,
        'show' => self::METHOD_GET,
        'new' => self::METHOD_GET,
        'createConfirm' => self::METHOD_POST,
        'create' => self::METHOD_POST,
        'edit' => self::METHOD_GET,
        'editConfirm' => self::METHOD_POST,
        'update' => self::METHOD_POST,
        'deleteConfirm' => self::METHOD_GET,
        'delete' => self::METHOD_POST, // HTMLフォームからの送信だとDELETEメソッドは使えないので
    ];

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
