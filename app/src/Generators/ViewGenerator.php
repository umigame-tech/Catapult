<?php

namespace UmigameTech\Catapult\Generators;

use Doctrine\Inflector\InflectorFactory;
use UmigameTech\Catapult\Datatypes\Attribute;
use UmigameTech\Catapult\Templates\Renderer;
use UmigameTech\Catapult\Datatypes\AttributeType;
use UmigameTech\Catapult\Datatypes\Entity;
use UmigameTech\Catapult\FileSystem\RemoveDirectory;

class ViewGenerator extends Generator
{
    private $projectPath = '';
    // view CRUD用のBladeテンプレート

    private function attributeTypeMap(AttributeType $type): string
    {
        return match ($type) {
            AttributeType::String => 'text',
            AttributeType::Username => 'text',
            AttributeType::Email => 'email',
            AttributeType::Password => 'password',
            AttributeType::Tel => 'tel',
            AttributeType::Integer => 'number',
            AttributeType::Boolean => 'checkbox',
            AttributeType::Date => 'date',
            AttributeType::Datetime => 'datetime-local',
            AttributeType::Time => 'time',
            AttributeType::Decimal => 'number',
            AttributeType::Text => 'textarea',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    public function generateContent(Entity $entity)
    {
        $projectPath = $this->projectPath();
        $this->projectPath = $projectPath;
        $authenticatable = $entity->isAuthenticatable();

        $dirPath = $this->projectPath . '/resources/views/' . $entity->name;

        // 前回のディレクトリが残っている場合は削除する
        if ($this->checker->exists($dirPath)) {
            $remover = new RemoveDirectory();
            $remover->remove($dirPath);
        }

        mkdir($dirPath, 0755, true);

        // exclude password
        $visible = clone $entity;
        $visible->attributes = $visible->attributes->filter(
            fn (Attribute $attribute) => $attribute->type !== AttributeType::Password
        );

        $this->generateIndexView($visible);
        $this->generateShowView($visible);

        $this->generateNewView($entity);
        $this->generateCreateConfirmView($entity);
        $this->generateEditView($entity);
        $this->generateUpdateConfirmView($entity);

        $this->generateDestroyConfirmView($visible);

        if ($authenticatable) {
            $this->generateLoginView($entity);
            $this->generateDashboardView($entity);
        }
    }

    public function generate()
    {
        /** @var Entity $entity */
        foreach ($this->entities as $entity) {
            $this->generateContent($entity);
        }
    }

    private function generateIndexView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/index.blade.php';

        $modelName = $entity->modelName();
        $camelCase = lcfirst($modelName);

        $inflector = InflectorFactory::create()->build();
        $plural = $inflector->pluralize($entity->name);

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/index.blade.php.twig', [
            'entity' => $entity,
            'plural' => $plural,
            'camelCase' => $camelCase,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateShowView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/show.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/show.blade.php.twig', [
            'entity' => $entity,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateNewView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/create.blade.php';

        $entity->attributes = $entity->attributes->mapWithSameType(
            function (Attribute $attribute) {
                $attribute->inputType = $this->attributeTypeMap($attribute->type);
                // 今後書き換えやすいように
                $attribute->inputName = $attribute->name;
                return $attribute;
            }
        );

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/create.blade.php.twig', [
            'entity' => $entity,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateCreateConfirmView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/storeConfirm.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/storeConfirm.blade.php.twig', [
            'entity' => $entity,
            'submitUri' => "{{ route(\$routePrefix . '{$entity->name}.store') }}",
            'backUri' => "{{ route(\$routePrefix . '{$entity->name}.create') }}",
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateEditView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/edit.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/edit.blade.php.twig', [
            'entity' => $entity,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateUpdateConfirmView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/updateConfirm.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/updateConfirm.blade.php.twig', [
            'entity' => $entity,
            'submitUri' => "{{ route(\$routePrefix . '{$entity->name}.update', ['id' => \${$entity->name}->id]) }}",
            'backUri' => "{{ route(\$routePrefix . '{$entity->name}.edit', ['id' => \${$entity->name}->id]) }}",
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateDestroyConfirmView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/destroyConfirm.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/destroyConfirm.blade.php.twig', [
            'entity' => $entity,
            'submitUri' => "{{ route(\$routePrefix . '{$entity->name}.destroy', ['id' => \${$entity->name}->id]) }}",
            'backUri' => "{{ route(\$routePrefix . '{$entity->name}.show', ['id' => \${$entity->name}->id]) }}",
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    private function generateLoginView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/login.blade.php';

        $entity->attributes = $entity->attributes->mapWithSameType(
            function (Attribute $attribute) {
                $attribute->inputType = $this->attributeTypeMap($attribute->type);
                // 今後書き換えやすいように
                $attribute->inputName = $attribute->name;
                return $attribute;
            }
        );

        $loginKeys = $entity->attributes->filter(
            fn (Attribute $attribute) => $attribute->loginKey
        );

        $password = $entity->attributes->filter(
            fn (Attribute $attribute) => $attribute->type === AttributeType::Password
        );
        if (empty($password)) {
            throw new \Exception('Password attribute is not found');
        }

        $password = $password[0];

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/login.blade.php.twig', [
            'entity' => $entity,
            'plural' => $this->inflector->pluralize($entity->name),
            'loginKeys' => $loginKeys,
            'password' => $password,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }

    public function generateDashboardView(Entity $entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity->name . '/dashboard.blade.php';

        $renderer = Renderer::getInstance();
        $entities = $this->entities->mapWithSameType(
            function (Entity $entity) {
                $entity->plural = $this->inflector->pluralize($entity->name);
                return $entity;
            },
        );

        $view = $renderer->render('views/dashboard.blade.php.twig', [
            'entity' => $entity,
            'entities' => $entities,
        ]);

        $this->writer->write(path: $viewPath, content: $view);
    }
}
