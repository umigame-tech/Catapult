<?php

const INDENT = '    ';

function indents(int $level): string
{
    return str_repeat(INDENT, $level);
}

$targetDir = '/dist/project';

$skipInstallation = !empty($argv[2]) && $argv[2] === '--skip-installation';

if (! $skipInstallation) {
    if (file_exists($targetDir . '/composer.json')) {
        exec("composer install --working-dir={$targetDir}");
    } else {
        exec("composer create-project --prefer-dist laravel/laravel {$targetDir}");
    }
}

if (empty($argv[1])) {
    echo "Usage: php main.php <path/to/file>\n";
    exit(1);
}

if (! $inputFile = file_get_contents($argv[1])) {
    echo "File not found: {$argv[1]}\n";
    exit(1);
}

$json = json_decode($inputFile, true);
foreach ($json['entities'] as $entity) {
    $modelName = implode('', array_map(
        fn ($word) => ucfirst($word),
        explode('_', $entity['name'])
    ));

    $fillableList = array_map(
        fn ($attribute) => "'{$attribute['name']}'",
        $entity['attributes']
    );
    $fillable = implode(",\n" . indents(2) , $fillableList);

    $model = <<<EOF
<?php
namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class $modelName extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected \$fillable = [
        $fillable,
    ];
}

EOF;

    $modelPath = $targetDir . '/app/Models/' . $modelName . '.php';
    file_put_contents($modelPath, $model);
}

