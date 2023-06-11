<?php

namespace UmigameTech\Catapult;

class Main
{
    const INDENT = '    ';

    private function indents(int $level): string
    {
        return str_repeat(self::INDENT, $level);
    }

    private $projectName = 'project';

    private $targetDir = '/dist';

    private function setupEnvFile()
    {
        copy('./default.env', $this->targetDir . '/.env');
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

        foreach ($json['entities'] as $entity) {
            $this->generateModel($entity);
            $this->generateMigration($entity);
            $this->generateController($entity);
        }
    }

    private function generateModel($entity) {
        $modelName = implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        ));

        $fillableList = array_map(
            fn ($attribute) => "'{$attribute['name']}'",
            $entity['attributes']
        );
        $fillable = implode(",\n" . $this->indents(2) , $fillableList);

        $model = <<<EOF
<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class {$modelName} extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected \$fillable = [
        {$fillable},
    ];
}

EOF;

        $modelPath = $this->targetDir . '/app/Models/' . $modelName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($modelPath)) {
            unlink($modelPath);
        }

        file_put_contents($modelPath, $model);
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

    private function generateController($entity) {
        $controllerName = implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        )) . 'Controller';

        $controller = <<<EOF
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class {$controllerName} extends Controller
{
    public function index() { }
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

    // view CRUD用のBladeテンプレート
}

(new Main())->handle($argv);
