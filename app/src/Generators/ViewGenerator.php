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

    public function generate($entity)
    {
        $projectPath = $this->projectPath();
        $this->projectPath = $projectPath;

        $dirPath = $this->projectPath . '/resources/views/' . $entity['name'];

        // 前回のディレクトリが残っている場合は削除する
        if (file_exists($dirPath)) {
            $remover = new RemoveDirectory();
            $remover->remove($dirPath);
        }

        mkdir($dirPath, 0755, true);

        $this->generateIndexView($entity);
        $this->generateShowView($entity);
        $this->generateNewView($entity);
        $this->generateCreateConfirmView($entity);
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

        file_put_contents($viewPath, $view);
    }

    private function generateShowView($entity)
    {
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/show.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/show.blade.php.twig', [
            'entity' => $entity,
        ]);

        file_put_contents($viewPath, $view);
    }

    private function generateNewView($entity)
    {
        $baseUri = $this->baseUri($entity);
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
            'baseUri' => $baseUri,
        ]);

        file_put_contents($viewPath, $view);
    }

    private function generateCreateConfirmView($entity)
    {
        $baseUri = $this->baseUri($entity);
        $viewPath = $this->projectPath . '/resources/views/' . $entity['name'] . '/createConfirm.blade.php';

        $renderer = Renderer::getInstance();
        $view = $renderer->render('views/createConfirm.blade.php.twig', [
            'entity' => $entity,
            'baseUri' => $baseUri,
        ]);

        file_put_contents($viewPath, $view);
    }
}
