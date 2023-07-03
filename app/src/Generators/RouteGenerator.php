<?php

namespace UmigameTech\Catapult\Generators;

use SplFileObject;

class RouteGenerator extends Generator
{

    private $webRoutePath = '';

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->webRoutePath = $this->projectPath() . '/routes/web.php';
    }

    public function refreshRoutes()
    {
        $file = new SplFileObject($this->webRoutePath, 'r+');
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

    public function sealedRoutesOpen($prefix = "")
    {
        if (empty($prefix)) {
            return;
        }

        $route = "Route::prefix('{$prefix}')->group(function () {";

        $file = new SplFileObject($this->webRoutePath, 'a+');
        $file->fwrite("\n\n{$route}\n");
    }

    public function sealedRoutesClose($prefix = "")
    {
        if (empty($prefix)) {
            return;
        }

        $file = new SplFileObject($this->webRoutePath, 'a+');
        $file->fwrite("\n\n});\n");
    }


    public function generate($entity, $indent = 0)
    {
        $controllerName = '\App\Http\Controllers\\' . ControllerGenerator::controllerName($entity);
        $indentString = $this->indents($indent);
        $routes = implode("\n", array_map(
            function ($action, $method) use ($entity, $controllerName, $indentString) {
                $path = match ($action) {
                    'index' =>  '',
                    default => '/' . $action,
                };

                return "{$indentString}Route::{$method}('/{$entity['name']}{$path}', [{$controllerName}::class, '{$action}']);";
            },
            array_keys(ControllerGenerator::$actions),
            array_values(ControllerGenerator::$actions)
        ));

        $file = new SplFileObject($this->webRoutePath, 'a+');
        $file->fwrite("\n\n{$routes}\n");
    }
}
