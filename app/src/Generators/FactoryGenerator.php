<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Attribute;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Rules\RuleType;
use UmigameTech\Catapult\Datatypes\Rules\RuleInterface;
use UmigameTech\Catapult\Templates\Renderer;

class FactoryGenerator extends Generator
{
    private function attributeTypeMap(AttributeType $type): string
    {
        return match ($type) {
            AttributeType::String => 'realText',
            AttributeType::Username => 'userName',
            AttributeType::Email => 'email',
            AttributeType::Password => 'password',
            AttributeType::Tel => 'phoneNumber',
            AttributeType::Integer => 'numberBetween',
            AttributeType::Boolean => 'boolean',
            AttributeType::Date => 'date',
            AttributeType::Datetime => 'dateTime',
            AttributeType::Time => 'time',
            AttributeType::Decimal => 'randomFloat',
            AttributeType::Text => 'realText',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    private function buildSize($type, Attribute $attribute)
    {
        if (!in_array($type, ['realText', 'randomFloat', 'numberBetween'])) {
            return '';
        }

        $size = [];
        /** @var RuleInterface $rule */
        foreach ($attribute->rules as $rule) {
            $ruleType = $rule->getType();
            if ($ruleType === RuleType::Min) {
                $size['min'] = $rule->getValue();
            }
            if ($ruleType === RuleType::Max) {
                $size['max'] = $rule->getValue();
            }
        }
        if ($size === []) {
            return '';
        }

        if ($type === 'randomFloat') {
            $sizeArgs = array_map(
                fn ($key) => match ($key) {
                    'min' => "min: {$size['min']}",
                    'max' => "max: {$size['max']}",
                    default => '',
                },
                array_keys($size)
            );
            return implode(', ', $sizeArgs);
        }

        if ($type === 'numberBetween') {
            $sizeArgs = array_map(
                fn ($key) => match ($key) {
                    'min' => "int1: {$size['min']}",
                    'max' => "int2: {$size['max']}",
                    default => '',
                },
                array_keys($size)
            );
            return implode(', ', $sizeArgs);
        }

        if ($type === 'realText' && isset($size['max'])) {
            return "maxNbChars: {$size['max']}";
        }

        return '';
    }

    public function generateContent(Entity $entity)
    {
        $modelName = $entity->modelName();
        $factoryName = $entity->factoryName();

        $fakers = $entity->attributes->map(
            function (Attribute $attribute) {
                $type = $this->attributeTypeMap($attribute->type);
                return [
                    'name' => $attribute->name,
                    'type' => $type,
                    'size' => $this->buildSize($type, $attribute),
                ];
            },
        );

        $renderer = Renderer::getInstance();
        $factory = $renderer->render('factory.twig', [
            'modelName' => $modelName,
            'factoryName' => $factoryName,
            'fakers' => $fakers,
            'entity' => $entity,
            'authenticatable' => $entity->isAuthenticatable(),
        ]);

        $projectPath = $this->projectPath();
        $factoryPath = "{$projectPath}" . '/database/factories/' . $factoryName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($factoryPath)) {
            unlink($factoryPath);
        }

        return [
            'path' => $factoryPath,
            'content' => $factory,
        ];
    }

    public function generate()
    {
        foreach ($this->entities as $entity) {
            $content = $this->generateContent($entity);
            if (empty($content)) {
                continue;
            }
            $this->writer->write(...$content);
        }
    }
}
