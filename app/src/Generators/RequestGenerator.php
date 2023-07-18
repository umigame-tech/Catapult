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

    public static function loginRequestName($entity)
    {
        return ModelGenerator::modelName($entity) . 'LoginRequest';
    }

    // attributeのtypeをLaravelのvalidation ruleに変換する
    private function attributeTypeMap(string $type): string
    {
        return match ($type) {
            AttributeType::String->value => 'string',
            AttributeType::Username->value => 'string',
            AttributeType::Email->value => 'email',
            AttributeType::Tel->value => 'string',
            AttributeType::Password->value => 'string',
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

    // $type は今は使わないが、型によってルールを加える可能性がある（emailやtelなど）
    private function buildValidationRules(string $type, $attribute)
    {
        $rules = $attribute['rules'] ?? [];
        $validationRules = [];
        foreach ($rules as $name => $value) {
            $validationRules[] = match ($name) {
                'min' => "min:{$value}",
                'max' => "max:{$value}",
                'required' => 'required',
                'unique' => "unique:{$attribute['name']}",
                default => null,
            };
        }

        return array_values(
            array_filter(
                $validationRules,
                fn ($rule) => $rule !== null
            )
        );
    }

    public function generateContent($entity)
    {
        $requestName = self::requestName($entity);
        $attributes = array_map(
            function ($attribute) {
                $rules = [$this->attributeTypeMap($attribute['type'])];
                $rules = array_merge($rules, $this->buildValidationRules($attribute['type'], $attribute));
                $rules= implode(",\n" . $this->indents(4), array_map(fn ($rule) => "'" . $rule . "'", $rules));
                return [
                    'name' => $attribute['name'],
                    'rules' => $rules,
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

        return [
            'path' => $requestPath,
            'content' => $request,
        ];
    }

    public function generateLoginContent($entity)
    {
        $requestName = self::loginRequestName($entity);

        $loginKeys = array_values(array_filter(
            $entity['attributes'],
            fn ($attribute) => $attribute['loginKey'] ?? false
        ));

        $password = array_values(array_filter(
            $entity['attributes'],
            fn ($attribute) => $attribute['type'] === AttributeType::Password->value,
        ));
        if (empty($password)) {
            throw new \Exception('Password attribute is not found');
        }

        $attributes = array_map(
            function ($attribute) {
                $rules = [$this->attributeTypeMap($attribute['type'])];
                $rules = array_merge($rules, $this->buildValidationRules($attribute['type'], $attribute));
                $rules= implode(",\n" . $this->indents(4), array_map(fn ($rule) => "'" . $rule . "'", $rules));
                return [
                    'name' => $attribute['name'],
                    'rules' => $rules,
                ];
            },
            array_merge($loginKeys, $password)
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

        return [
            'path' => $requestPath,
            'content' => $request,
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

            // ログイン可能なエンティティの場合はログイン用のリクエストも生成する
            if (!($entity['authenticatable'] ?? false)) {
                continue;
            }

            $loginContent = $this->generateLoginContent($entity);
            if (empty($loginContent)) {
                continue;
            }

            $this->writer->write(...$loginContent);
        }
    }
}
