<?php

namespace UmigameTech\Catapult\Generators;

class ModelGenerator extends Generator
{

    public function generate($entity) {
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

        $projectPath = $this->projectPath();
        $modelPath = "{$projectPath}" . '/app/Models/' . $modelName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($modelPath)) {
            unlink($modelPath);
        }

        file_put_contents($modelPath, $model);
    }

}
