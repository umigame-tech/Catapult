<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\Attribute;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\Templates\Renderer;

class ApiStoreRequestGenerator extends RequestGenerator
{
    public function generateContent(Entity $entity)
    {
        $requestName = $entity->apiStoreRequestName();
        $attributes = $entity->attributes->map(
            function (Attribute $attribute) {
                $rules = [$this->attributeTypeMap($attribute->type)];
                // とりあえずパスワード以外全部必須にしておく
                // TODO: nullable の rule を作成する
                if ($attribute->type === AttributeType::Password) {
                    $rules[] = 'nullable';
                } else {
                    $rules[] = 'required';
                }
                $rules = array_values(array_unique(
                    array_merge(
                        $rules,
                        $this->buildValidationRules($attribute->type, $attribute)
                    )
                ));
                $rules = implode(",\n" . $this->indents(4), array_map(fn ($rule) => "'" . $rule . "'", $rules));
                return [
                    'name' => $attribute->name,
                    'rules' => $rules,
                ];
            }
        );

        $renderer = Renderer::getInstance();
        $request = $renderer->render('api/request.twig', [
            'requestName' => $requestName,
            'entity' => $entity,
            'attributes' => $attributes,
        ]);

        $projectPath = $this->projectPath();
        $requestPath = "{$projectPath}/app/Http/Requests/Api/{$requestName}.php";
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

    public function generate()
    {
        $relativePath = 'app/Http/Requests/Api/ApiRequest.php';
        // Copy ApiRequest.php file.
        $source = __DIR__ . '/../Templates/' . $relativePath;
        $dest = $this->projectPath() . '/' . $relativePath;
        $this->copier->copyFile($source, $dest);

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

            // TODO: ログイン用のリクエストを生成する処理
        }
    }
}
