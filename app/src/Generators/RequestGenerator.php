<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Attribute;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Datatypes\Rules\RuleInterface;
use UmigameTech\Catapult\Datatypes\Rules\RuleType;
use UmigameTech\Catapult\Templates\Renderer;

class RequestGenerator extends Generator
{
    // attributeのtypeをLaravelのvalidation ruleに変換する
    protected function attributeTypeMap(AttributeType $type): string
    {
        return match ($type) {
            AttributeType::Select, AttributeType::Radio => 'integer',
            AttributeType::Multiple => 'array',
            AttributeType::String => 'string',
            AttributeType::Username => 'string',
            AttributeType::Email => 'email',
            AttributeType::Tel => 'string',
            AttributeType::Password => 'string',
            AttributeType::Integer => 'integer',
            AttributeType::Boolean => 'boolean',
            AttributeType::Date => 'date',
            AttributeType::Datetime => 'date',
            AttributeType::Time => 'regex:/\d{2}:\d{2}/',
            AttributeType::Decimal => 'numeric',
            AttributeType::Text => 'string',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    // $type は今は使わないが、型によってルールを加える可能性がある（emailやtelなど）
    protected function buildValidationRules(AttributeType $type, Attribute $attribute)
    {
        $rules = $attribute->rules ?? [];
        $validationRules = [];
        /** @var RuleInterface $rule */
        foreach ($rules as $rule) {
            $validationRules[] = match ($rule->getType()) {
                RuleType::Min => "min:{$rule->getValue()}",
                RuleType::Max => "max:{$rule->getValue()}",
                RuleType::Required => 'required',
                RuleType::Nullable => 'nullable',
                // 'unique' => "unique:{$attribute->name}",
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

    public function generateContent(Entity $entity)
    {
        $requestName = $entity->requestName();
        $attributes = $entity->attributes->map(
            function (Attribute $attribute) {
                $rules = [$this->attributeTypeMap($attribute->type)];
                if ($attribute->type === AttributeType::Password) {
                    $rules[] = 'nullable';
                }
                $rules = array_merge($rules, $this->buildValidationRules($attribute->type, $attribute));
                $rules = implode(",\n" . $this->indents(4), array_map(fn ($rule) => "'" . $rule . "'", $rules));
                return [
                    'name' => $attribute->name,
                    'rules' => $rules,
                ];
            }
        );

        $renderer = Renderer::getInstance();
        $request = $renderer->render('request.twig', [
            'requestName' => $requestName,
            'entity' => $entity,
            'attributes' => $attributes,
        ]);

        $projectPath = $this->projectPath();
        $requestPath = "{$projectPath}/app/Http/Requests/{$requestName}.php";
        if ($this->checker->exists($requestPath)) {
            $this->remover->remove($requestPath);
        }

        if (!$this->checker->exists(dirname($requestPath))) {
            $this->makeDirectory->mkdir(dirname($requestPath), 0755, true);
        }

        return [
            'path' => $requestPath,
            'content' => $request,
        ];
    }

    public function generateLoginContent(Entity $entity)
    {
        $requestName = $entity->loginRequestName();

        $loginKeys = $entity->attributes->filter(
            fn (Attribute $attribute) => $attribute->loginKey
        );

        $password = $entity->attributes->filter(
            fn (Attribute $attribute) => $attribute->type === AttributeType::Password
        );
        if (empty($password)) {
            throw new \Exception('Password attribute is not found');
        }

        $attributes = $loginKeys->merge($password)->map(
            function ($attribute) {
                $rules = [$this->attributeTypeMap($attribute->type)];
                $rules = array_merge($rules, $this->buildValidationRules($attribute->type, $attribute));
                $rules= implode(",\n" . $this->indents(4), array_map(fn ($rule) => "'" . $rule . "'", $rules));
                return [
                    'name' => $attribute->name,
                    'rules' => $rules,
                ];
            },
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
            $this->makeDirectory->mkdir(dirname($requestPath), 0755, true);
        }

        return [
            'path' => $requestPath,
            'content' => $request,
        ];
    }

    public function generate()
    {
        /** @var Entity $entity */
        foreach ($this->entities as $entity) {
            $content = $this->generateContent($entity);
            if (empty($content)) {
                continue;
            }

            $this->writer->write(...$content);

            // ログイン可能なエンティティの場合はログイン用のリクエストも生成する
            if (!($entity->isAuthenticatable())) {
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
