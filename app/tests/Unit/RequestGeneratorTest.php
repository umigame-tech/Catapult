<?php

use UmigameTech\Catapult\FileSystem\FileReaderInterface;
use UmigameTech\Catapult\FileSystem\FileWriterInterface;
use UmigameTech\Catapult\Generators\RequestGenerator;

beforeEach(function () {
    $this->reader = new class implements FileReaderInterface {
        public function read($path)
        {
            return "";
        }
    };

    $this->contents = [];

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
    $generator = new RequestGenerator(
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

    list('content' => $content) = $generator->generateContent($entity);
    expect($content)
        ->toBeString()
        ->toContain('class UserRequest extends FormRequest');
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
    $generator = new RequestGenerator(
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

    $generator->generate();
    expect($this->contents)->toBeArray();
    expect($this->contents)->toHaveLength(1);
});
