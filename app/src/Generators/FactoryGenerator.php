<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Templates\Renderer;

class FactoryGenerator extends Generator
{
    private function attributeTypeMap(string $type): string
    {
        return match ($type) {
            AttributeType::String->value => 'realText',
            AttributeType::Username->value => 'userName',
            AttributeType::Email->value => 'email',
            AttributeType::Tel->value => 'phoneNumber',
            AttributeType::Integer->value => 'numberBetween',
            AttributeType::Boolean->value => 'boolean',
            AttributeType::Date->value => 'date',
            AttributeType::Datetime->value => 'dateTime',
            AttributeType::Time->value => 'time',
            AttributeType::Decimal->value => 'randomFloat',
            AttributeType::Text->value => 'realText',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    private function buildSize($type, $attribute)
    {
        if (!in_array($type, ['realText', 'randomFloat', 'numberBetween'])) {
            return '';
        }

        $size = [];
        foreach ($attribute['rules'] ?? [] as $name => $value) {
            if ($name === 'min') {
                $size['min'] = $value;
            }
            if ($name === 'max') {
                $size['max'] = $value;
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

    public function generate($entity)
    {
        $modelName = ModelGenerator::modelName($entity);
        $factoryName = implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        )) . 'Factory';

        $fakers = array_map(
            function ($attribute) {
                $type = $this->attributeTypeMap($attribute['type']);
                return [
                    'name' => $attribute['name'],
                    'type' => $type,
                    'size' => $this->buildSize($type, $attribute),
                ];
            },
            $entity['attributes']
        );

        $renderer = Renderer::getInstance();
        $factory = $renderer->render('factory.twig', [
            'modelName' => $modelName,
            'factoryName' => $factoryName,
            'fakers' => $fakers,
            'entity' => $entity,
        ]);

        $projectPath = $this->projectPath();
        $factoryPath = "{$projectPath}" . '/database/factories/' . $factoryName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($factoryPath)) {
            unlink($factoryPath);
        }

        file_put_contents($factoryPath, $factory);
    }
}
