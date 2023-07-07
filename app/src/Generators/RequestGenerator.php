<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Templates\Renderer;

class RequestGenerator extends Generator
{
    public static function requestName($entity)
    {
        return ModelGenerator::modelName($entity) . 'Request';
    }

    // attributeのtypeをLaravelのvalidation ruleに変換する
    private function attributeTypeMap(string $type): string
    {
        return match ($type) {
            AttributeType::String->value => 'string',
            AttributeType::Username->value => 'string',
            AttributeType::Email->value => 'email',
            AttributeType::Tel->value => 'string',
            AttributeType::Integer->value => 'integer',
            AttributeType::Boolean->value => 'boolean',
            AttributeType::Date->value => 'date',
            AttributeType::Datetime->value => 'date',
            AttributeType::Time->value => 'regex:/\d{2}:\d{2}/',
            AttributeType::Decimal->value => 'numeric',
            AttributeType::Text->value => 'string',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    public function generate($entity)
    {
        $requestName = self::requestName($entity);
        $attributes = array_map(
            function ($attribute) {
                return [
                    'name' => $attribute['name'],
                    'rule' => $this->attributeTypeMap($attribute['type']),
                ];
            },
            $entity['attributes']
        );

        $renderer = Renderer::getInstance();
        $request = $renderer->render('request.twig', [
            'requestName' => $requestName,
            'entity' => $entity,
            'attributes' => $attributes,
        ]);

        $projectPath = $this->projectPath();
        $requestPath = "{$projectPath}/app/Http/Requests/{$requestName}.php";
        if (file_exists($requestPath)) {
            unlink($requestPath);
        }

        if (!file_exists(dirname($requestPath))) {
            mkdir(dirname($requestPath), 0755, true);
        }
        file_put_contents($requestPath, $request);
    }
}
