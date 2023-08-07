<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Datatypes\Attribute;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Rules\RuleType;
use UmigameTech\Catapult\ProjectSettings;
use UmigameTech\Catapult\Templates\Renderer;

class MigrationGenerator extends Generator
{
    private function attributeTypeMap(AttributeType $type): string
    {
        return match ($type) {
            AttributeType::Select, AttributeType::Radio => 'foreignId',
            // TODO: Add support for multiple select
            AttributeType::Multiple => 'text',
            AttributeType::String => 'string',
            AttributeType::Username => 'string',
            AttributeType::Email => 'string',
            AttributeType::Password => 'string',
            AttributeType::Tel => 'string',
            AttributeType::Integer => 'integer',
            AttributeType::Boolean => 'boolean',
            AttributeType::Date => 'date',
            AttributeType::Datetime => 'dateTime',
            AttributeType::Time => 'time',
            AttributeType::Decimal => 'decimal',
            AttributeType::Text => 'text',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    private function buildCheckConstraint(string $sqlDataType, Attribute $attribute)
    {
        $settings = ProjectSettings::getInstance();
        if ($settings->get('db_engine') === 'sqlite') {
            // SQLite の場合は ALTER TABLE 構文で CHECK 成約を追加できない
            return [];
        }

        if ($sqlDataType === 'integer') {
            $rules = $attribute->rules;
            $constraints = [];
            foreach ($rules as $rule) {
                $constraints[] = match ($rule->getType()) {
                    RuleType::Min => "{$attribute->name}_min CHECK ({$sqlDataType} >= {$rule->getValue()})",
                    RuleType::Max=> "{$attribute->name}_max CHECK ({$sqlDataType} <= {$rule->getValue()})",
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

    public function generateContent(Entity $entity)
    {
        $tableName = $entity->name;

        $columns = $entity->attributes->map(
            function (Attribute $attribute) {
                $type = $this->attributeTypeMap($attribute->type);
                return [
                    'name' => $attribute->name,
                    'type' => $type,
                    'constraints' => $this->buildCheckConstraint($type, $attribute),
                ];
            }
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
        /** @var Entity $entity */
        foreach ($this->entities as $entity) {
            if (empty($entity)) {
                continue;
            }

            $result = $this->generateContent($entity);
            $this->writer->write(...$result);
        }
    }
}
