<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Templates\Renderer;

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

        $columns = array_map(
            function ($attribute) {
                return [
                    'name' => $attribute['name'],
                    'type' => $this->attributeTypeMap($attribute['type']),
                ];
            },
            $entity['attributes']
        );

        $inflector = InflectorFactory::create()->build();
        $tableName = $inflector->tableize($tableName);
        $pluralTableName = $inflector->pluralize($tableName);

        $renderer = Renderer::getInstance();
        $migration = $renderer->render('migration.twig', [
            'pluralTableName' => $pluralTableName,
            'columns' => $columns,
        ]);

        $projectPath = $this->projectPath();

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
