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
            AttributeType::Integer->value => 'randomNumber',
            AttributeType::Boolean->value => 'boolean',
            AttributeType::Date->value => 'date',
            AttributeType::Datetime->value => 'dateTime',
            AttributeType::Time->value => 'time',
            AttributeType::Decimal->value => 'randomFloat',
            AttributeType::Text->value => 'realText',
            default => throw new \Exception('Invalid attribute type'),
        };
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
                return [
                    'name' => $attribute['name'],
                    'type' => $this->attributeTypeMap($attribute['type']),
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
