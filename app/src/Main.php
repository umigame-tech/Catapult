<?php

namespace UmigameTech\Catapult;

require_once(__DIR__ . '/../vendor/autoload.php');

use SplFileObject;

class Main
{
    const INDENT = '    ';

    const METHOD_GET = 'get';
    const METHOD_POST = 'post';

    private function indents(int $level): string
    {
        return str_repeat(self::INDENT, $level);
    }

    private $projectName = 'project';

    private $targetDir = '/dist';

    private $actions = [
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

    private function setupEnvFile()
    {
        $fileDir = __DIR__ . '/../../app';
        $envFile = preg_replace('/\/$/', '', $fileDir) . '/default.env';
        copy($envFile, $this->targetDir . '/.env');
    }

    private function setupDatabase()
    {
        touch($this->targetDir . '/database/database.sqlite');
    }

    public function handle($argv)
    {
        if (empty($argv[1])) {
            echo "Usage: php main.php <path/to/file>\n";
            exit(1);
        }

        if (! $inputFile = file_get_contents($argv[1])) {
            echo "File not found: {$argv[1]}\n";
            exit(1);
        }

        $json = json_decode($inputFile, true);
        if (!empty($json['project_name'])) {
            $this->projectName = $json['project_name'];
        }
        $this->targetDir .= '/' . $this->projectName;

        $skipInstallation = !empty($argv[2]) && $argv[2] === '--skip-installation';
        if (! $skipInstallation) {
            if (file_exists($this->targetDir . '/composer.json')) {
                exec("composer install --working-dir={$this->targetDir}");
            } else {
                exec("composer create-project --prefer-dist laravel/laravel {$this->targetDir}");
            }
        }

        $this->setupEnvFile();
        $this->setupDatabase();
        $this->refreshRoutes();

        $prefix = $json['sealed_prefix'] ?? "";
        $indent = empty($prefix) ? 0 : 1;
        $this->sealedRoutesOpen($prefix);

        $modelGenerator = new \UmigameTech\Catapult\Generators\ModelGenerator($this->projectName);

        foreach ($json['entities'] as $entity) {
            // $this->generateModel($entity);
            $modelGenerator->generate($entity);
            $this->generateMigration($entity);
            $this->generateController($entity);
            $this->generateViews($entity);
            $this->routesOf($entity, $indent);
        }

        $this->sealedRoutesClose($prefix);
    }

    private function generateMigration($entity) {
        $tableName = $entity['name'];

        $columnList = array_map(
            fn ($attribute) => "\$table->{$attribute['type']}('{$attribute['name']}');",
            $entity['attributes']
        );
        $columns = implode("\n" . $this->indents(3), $columnList);

        $migration = <<<EOF
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            $columns
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};

EOF;

        // /_create_{$tableName}_table/ というパターンに一致するファイル名で既にファイルがある場合は削除してから生成する
        foreach (glob($this->targetDir . '/database/migrations/*_create_' . $tableName . '_table.php') as $file) {
            unlink($file);
        }
        
        $migrationPath = $this->targetDir . '/database/migrations/' . date('Y_m_d_His') . '_create_' . $tableName . '_table.php';

        file_put_contents($migrationPath, $migration);
    }

    private function controllerName($entity)
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        )) . 'Controller';
    }

    private function generateController($entity)
    {
        $controllerName = $this->controllerName($entity);

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

        $controllerPath = $this->targetDir . '/app/Http/Controllers/' . $controllerName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($controllerPath)) {
            unlink($controllerPath);
        }

        file_put_contents($controllerPath, $controller);
    }

    // web.php CRUD用のRoute

    // 古いRouteを削除する
    private function refreshRoutes()
    {
        $webRoutePath = $this->targetDir . '/routes/web.php';
        $file = new SplFileObject($webRoutePath, 'r+');
        $file->ftruncate(0);

        $template = <<<EOF
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

EOF;

        $file->fwrite($template);
    }

    private function sealedRoutesOpen($prefix = "")
    {
        if (empty($prefix)) {
            return;
        }

        $route = "Route::prefix('{$prefix}')->group(function () {";

        $webRoutePath = $this->targetDir . '/routes/web.php';
        $file = new SplFileObject($webRoutePath, 'a+');
        $file->fwrite("\n\n{$route}\n");
    }

    private function sealedRoutesClose($prefix = "")
    {
        if (empty($prefix)) {
            return;
        }

        $webRoutePath = $this->targetDir . '/routes/web.php';
        $file = new SplFileObject($webRoutePath, 'a+');
        $file->fwrite("\n\n});\n");
    }

    private function routesOf($entity, $indent = 0)
    {
        $controllerName = '\App\Http\Controllers\\' . $this->controllerName($entity);
        $indentString = $this->indents($indent);
        $routes = implode("\n", array_map(
            function ($action, $method) use ($entity, $controllerName, $indentString) {
                $path = match ($action) {
                    'index' =>  '',
                    default => '/' . $action,
                };

                return "{$indentString}Route::{$method}('/{$entity['name']}{$path}', [{$controllerName}::class, '{$action}']);";
            },
            array_keys($this->actions),
            array_values($this->actions)
        ));

        $webRoutePath = $this->targetDir . '/routes/web.php';
        $file = new SplFileObject($webRoutePath, 'a+');
        $file->fwrite("\n\n{$routes}\n");
    }

    // view CRUD用のBladeテンプレート
    private function generateViews($entity)
    {
        $this->generateIndexView($entity);
    }

    private function generateIndexView($entity)
    {
        // 前回のディレクトリが残っている場合は削除する
        if (file_exists($this->targetDir . '/resources/views/' . $entity['name'])) {
            exec("rm -rf {$this->targetDir}/resources/views/{$entity['name']}");
        }

        mkdir($this->targetDir . '/resources/views/' . $entity['name'], 0755, true);
        $viewPath = $this->targetDir . '/resources/views/' . $entity['name'] . '/index.blade.php';
        $view = <<<EOF
<h1>index of {$entity['name']}</h1>
EOF;

        file_put_contents($viewPath, $view);
    }
}

(new Main())->handle($argv);
