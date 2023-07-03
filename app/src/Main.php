<?php

namespace UmigameTech\Catapult;

use SplFileObject;
use UmigameTech\Catapult\Generators\ModelGenerator;
use UmigameTech\Catapult\Generators\MigrationGenerator;
use UmigameTech\Catapult\Generators\ControllerGenerator;
use UmigameTech\Catapult\Generators\ViewGenerator;

require_once(__DIR__ . '/../vendor/autoload.php');

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

        $modelGenerator = new ModelGenerator($this->projectName);
        $migrationGenerator = new MigrationGenerator($this->projectName);
        $controllerGenerator = new ControllerGenerator($this->projectName);
        $viewGenerator = new ViewGenerator($this->projectName);

        foreach ($json['entities'] as $entity) {
            $modelGenerator->generate($entity);
            $migrationGenerator->generate($entity);
            $controllerGenerator->generate($entity);
            $viewGenerator->generate($entity);
            $this->routesOf($entity, $indent);
        }

        $this->sealedRoutesClose($prefix);
    }

    private function controllerName($entity)
    {
        return implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        )) . 'Controller';
    }

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
}

(new Main())->handle($argv);
