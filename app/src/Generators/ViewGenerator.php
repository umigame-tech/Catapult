<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Templates\Renderer;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\FileSystem\RemoveDirectory;

class ViewGenerator extends Generator
{
    private $projectPath = '';
    // view CRUD用のBladeテンプレート

    private function attributeTypeMap(string $type): string
    {
        return match ($type) {
            AttributeType::String->value => 'text',
            AttributeType::Username->value => 'text',
            AttributeType::Email->value => 'email',
            AttributeType::Password->value => 'password',
            AttributeType::Tel->value => 'tel',
            AttributeType::Integer->value => 'number',
            AttributeType::Boolean->value => 'checkbox',
            AttributeType::Date->value => 'date',
            AttributeType::Datetime->value => 'datetime-local',
            AttributeType::Time->value => 'time',
            AttributeType::Decimal->value => 'number',
            AttributeType::Text->value => 'textarea',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    public function generateContent($entity)
    {
        $projectPath = $this->projectPath();
        $this->projectPath = $projectPath;
        $authenticatable = $entity['authenticatable'] ?? false;

        $dirPath = $this->projectPath . '/resources/views/' . $entity['name'];

        // 前回のディレクトリが残っている場合は削除する
        if ($this->checker->exists($dirPath)) {
            $remover = new RemoveDirectory();
            $remover->remove($dirPath);
        }

        mkdir($dirPath, 0755, true);

        $visible = clone $entity;
        $visible['attributes'] = array_values(array_filter(
            $visible['attributes'],
            fn ($attribute) => $attribute['type'] !== AttributeType::Password->value
        ));

        $this->generateIndexView($visible);
        $this->generateShowView($visible);

        $this->generateNewView($entity);
        $this->generateCreateConfirmView($entity);
        $this->generateEditView($entity);
        $this->generateUpdateConfirmView($entity);

        $this->generateDestroyConfirmView($visible);

        if ($authenticatable) {
            $this->generateLoginView($entity);
        }
    }

    public function generate()
    {
        foreach ($this->entities as $entity) {
            $this->generateContent($entity);
        }
    }

    private function generateIndexView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/index.blade.php';

        $modelName = ModelGenerator::modelName($entity);
        $camelCase = lcfirst($modelName);

        $inflector = InflectorFactory::create()->build();
        $plural = $inflector->pluralize($entity['name']);

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/index.blade.php.twig', [
            'entity' => $entity,
            'plural' => $plural,
            'camelCase' => $camelCase,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateShowView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/show.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/show.blade.php.twig', [
            'entity' => $entity,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateNewView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/new.blade.php';

        $entity['attributes'] = array_map(
            function ($attribute) {
                $attribute['inputType'] = $this->attributeTypeMap($attribute['type']);
                // 今後書き換えやすいように
                $attribute['inputName'] = $attribute['name'];
                return $attribute;
            },
            $entity['attributes']
        );

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/new.blade.php.twig', [
            'entity' => $entity,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateCreateConfirmView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/createConfirm.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/createConfirm.blade.php.twig', [
            'entity' => $entity,
            'submitUri' => "{{ route(\$routePrefix . '{$entity['name']}.create') }}",
            'backUri' => "{{ route(\$routePrefix . '{$entity['name']}.new') }}",
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateEditView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/edit.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/edit.blade.php.twig', [
            'entity' => $entity,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateUpdateConfirmView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/updateConfirm.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/updateConfirm.blade.php.twig', [
            'entity' => $entity,
            'submitUri' => "{{ route(\$routePrefix . '{$entity['name']}.update', ['id' => \${$entity['name']}->id]) }}",
            'backUri' => "{{ route(\$routePrefix . '{$entity['name']}.edit', ['id' => \${$entity['name']}->id]) }}",
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateDestroyConfirmView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/destroyConfirm.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/destroyConfirm.blade.php.twig', [
            'entity' => $entity,
            'submitUri' => "{{ route(\$routePrefix . '{$entity['name']}.destroy', ['id' => \${$entity['name']}->id]) }}",
            'backUri' => "{{ route(\$routePrefix . '{$entity['name']}.show', ['id' => \${$entity['name']}->id]) }}",
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateLoginView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/login.blade.php';

        $entity['attributes'] = array_map(
            function ($attribute) {
                $attribute['inputType'] = $this->attributeTypeMap($attribute['type']);
                // 今後書き換えやすいように
                $attribute['inputName'] = $attribute['name'];
                return $attribute;
            },
            $entity['attributes']
        );

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

        $password = $password[0];

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/login.blade.php.twig', [
            'entity' => $entity,
            'plural' => $this->inflector->pluralize($entity['name']),
            'loginKeys' => $loginKeys,
            'password' => $password,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }
}
