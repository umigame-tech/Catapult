<?php

use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileRemoverInterface;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\Generators\SeederGenerator;

beforeEach(function () {
    $this->reader = new class implements FileReaderInterface {
        public function read($path)
        {
            return "";
        }
    };

    $this->contents = [];
    $this->removed = [];

    $outer = $this;
    $this->writer = new class($outer) implements FileWriterInterface {
        public $outer;
        public function __construct($outer) {
            $this->outer = $outer;
        }
        public function write($path, $content): bool|int
        {
            $this->outer->contents[] = $content;
            return mb_strlen($content, '8bit');
        }
    };

    $this->remover = new class($outer) implements FileRemoverInterface {
        public $outer;
        public function __construct($outer) {
            $this->outer = $outer;
        }
        public function remove($path): bool
        {
            $this->outer->removed[] = $path;
            return true;
        }
    };
});

test('generateContent', function () {
    $entity = [
        'name' => 'user',
        'attributes' => [
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
    $generator = new SeederGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
            'entities' => [
                $entity,
            ],
        ],
        $this->reader,
        $this->writer,
        $this->remover
    );

    list('content' => $content) = $generator->generateContent($entity);
    expect($content)
        ->toBeString()
        ->toContain('class UserSeeder extends Seeder');
});

test('generate', function () {
    $entity = [
        'name' => 'user',
        'attributes' => [
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
    $generator = new SeederGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
            'entities' => [
                $entity,
            ],
        ],
        $this->reader,
        $this->writer,
        $this->remover
    );

    $generator->generate();
    expect($this->contents)
        ->toBeArray()
        ->toHaveLength(1);

    expect($this->removed)
        ->toBeArray()
        ->toHaveLength(2)
        ->toMatchArray([
            'database/seeders/DatabaseSeeder.php',
        ]);
});
