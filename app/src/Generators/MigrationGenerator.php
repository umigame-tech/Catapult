<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\ProjectSettings;
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

    private function buildCheckConstraint(string $sqlDataType, $attribute)
    {
        $settings = ProjectSettings::getInstance();
        if ($settings->get('db_engine') === 'sqlite') {
            // SQLite の場合は ALTER TABLE 構文で CHECK 成約を追加できない
            return [];
        }

        if ($sqlDataType === 'integer') {
            $rules = $attribute['rules'] ?? [];
            $constraints = [];
            foreach ($rules as $name => $value) {
                $constraints[] = match ($name) {
                    'min' => "{$attribute['name']}_min CHECK ({$sqlDataType} >= {$value})",
                    'max' => "{$attribute['name']}_max CHECK ({$sqlDataType} <= {$value})",
                    default => null,
                };
            }

            return array_values(
                array_filter(
                    $constraints,
                    fn ($constraint) => $constraint !== null
                )
            );
        }

        return [];
    }

    public function generateContent($entity)
    {
        $tableName = $entity['name'];

        $columns = array_map(
            function ($attribute) {
                $type = $this->attributeTypeMap($attribute['type']);
                return [
                    'name' => $attribute['name'],
                    'type' => $type,
                    'constraints' => $this->buildCheckConstraint($type, $attribute),
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

        // /_create_{$pluralTableName}_table/ というパターンに一致するファイル名で既にファイルがある場合は削除してから生成する
        foreach (glob($projectPath . '/database/migrations/*_create_' . $pluralTableName . '_table.php') as $file) {
            unlink($file);
        }

        $migrationPath = "{$projectPath}/database/migrations/"
            . date('Y_m_d_His')
            . "_create_{$pluralTableName}_table.php";

        return [
            'path' => $migrationPath,
            'content' => $migration,
        ];
    }

    public function generate()
    {
        foreach ($this->entities as $entity) {
            if (empty($entity)) {
                continue;
            }

            $result = $this->generateContent($entity);
            $this->writer->write(...$result);
        }
    }
}
