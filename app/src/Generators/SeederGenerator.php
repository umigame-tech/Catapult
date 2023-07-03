<?php

namespace UmigameTech\Catapult\Generators;

class SeederGenerator extends Generator
{

    public function generateDatabaseSeeder($entities)
    {
        $seedersList = array_map(
            function ($entity) {
                $entityName = implode('', array_map(
                    fn ($word) => ucfirst($word),
                    explode('_', $entity['name'])
                ));

                return "\$this->call({$entityName}Seeder::class);";
            },
            $entities
        );
        $seeders = implode("\n{$this->indents(2)}", $seedersList);

        $seeder = <<<EOF
<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        {$seeders}
    }
}

EOF;

        $projectPath = $this->projectPath();
        $seederPath = "{$projectPath}" . '/database/seeders/DatabaseSeeder.php';
        unlink($seederPath);
        file_put_contents($seederPath, $seeder);
    }

    public function generate($entity)
    {
        $modelName = implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        ));
        $seederName = $modelName . 'Seeder';

        $seeder = <<<EOF
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\\{$modelName};

class {$seederName} extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        {$modelName}::factory()->count(10)->create();
    }
}

EOF;
    
        $projectPath = $this->projectPath();
        $seederPath = "{$projectPath}" . '/database/seeders/' . $seederName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($seederPath)) {
            unlink($seederPath);
        }

        file_put_contents($seederPath, $seeder);
    }     
}
