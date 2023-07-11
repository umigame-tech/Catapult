<?php
use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\Generators\ControllerGenerator;

beforeEach(function () {
    $this->reader = new class implements FileReaderInterface {
        public function read($path)
        {
            return "";
        }
    };

    $this->writer = new class implements FileWriterInterface {
        public function write($path, $content): bool|int
        {
            return mb_strlen($content, '8bit');
        }
    };
});

test('generate', function () {
    $entity = [
        'name' => 'user',
        'fields' => [
            [
                'name' => 'name',
                'type' => 'string',
            ],
            [
                'name' => 'email',
                'type' => 'string',
            ],
            [
                'name' => 'password',
                'type' => 'string',
            ],
        ],
    ];
    $generator = new ControllerGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
            'entities' => [
                $entity,
            ],
        ],
        $this->reader,
        $this->writer
    );

    $controller = $generator->generate($entity);
    $path = $controller['path'];
    $content = $controller['content'];
    expect($path)->toBeString();
    expect($content)->toBeString();
    expect($content)->toContain('class UserController');
});
