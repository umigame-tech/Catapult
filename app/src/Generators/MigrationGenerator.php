<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Datatypes\AttributeType;

class MigrationGenerator extends Generator
{
    private function attributeTypeMap(string $type): string
    {
        return match ($type) {
            AttributeType::String->value => 'string',
            AttributeType::Username->value => 'string',
            AttributeType::Email->value => 'string',
            AttributeType::Tel->value => 'string',
            AttributeType::Integer->value => 'integer',
            AttributeType::Boolean->value => 'boolean',
            AttributeType::Date->value => 'date',
            AttributeType::Datetime->value => 'dateTime',
            AttributeType::Time->value => 'time',
            AttributeType::Decimal->value => 'decimal',
            AttributeType::Text->value => 'text',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    public function generate($entity)
    {
        $tableName = $entity['name'];

        $columnList = array_map(
            fn ($attribute) => "\$table->{$this->attributeTypeMap($attribute['type'])}('{$attribute['name']}');",
            $entity['attributes']
        );
        $columns = implode("\n" . $this->indents(3), $columnList);

        $inflector = InflectorFactory::create()->build();
        $tableName = $inflector->tableize($tableName);
        $pluralTableName = $inflector->pluralize($tableName);

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
        Schema::create('{$pluralTableName}', function (Blueprint \$table) {
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

        $projectPath = $this->projectPath();
        // Laravelインストール時に生成されるマイグレーションファイルは、すべて削除する
        foreach (glob($projectPath . '/database/migrations/*_create_users_table.php') as $file) {
            unlink($file);
        }
        foreach (glob($projectPath . '/database/migrations/*_create_password_resets_table.php') as $file) {
            unlink($file);
        }
        foreach (glob($projectPath . '/database/migrations/*_create_failed_jobs_table.php') as $file) {
            unlink($file);
        }
        foreach (glob($projectPath . '/database/migrations/*_create_personal_access_tokens_table.php') as $file) {
            // ここで消してもLaravel自体にcreate_personal_access_tokens_table.phpがあるのでテーブルが作成される
            // ひとまず様子見
            unlink($file);
        }

        // /_create_{$tableName}_table/ というパターンに一致するファイル名で既にファイルがある場合は削除してから生成する
        foreach (glob($projectPath . '/database/migrations/*_create_' . $tableName . '_table.php') as $file) {
            unlink($file);
        }
        
        $migrationPath = "{$projectPath}/database/migrations/"
            . date('Y_m_d_His')
            . "_create_{$tableName}_table.php";

        file_put_contents($migrationPath, $migration);
    }
}
