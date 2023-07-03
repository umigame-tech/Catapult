<?php

namespace UmigameTech\Catapult\Generators;

class MigrationGenerator extends Generator
{
    public function generate($entity)
    {
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
        
        $migrationPath = "{$this->targetDir}/{$this->projectName}/database/migrations/"
            . date('Y_m_d_His')
            . "_create_{$tableName}_table.php";

        file_put_contents($migrationPath, $migration);
    }
}
