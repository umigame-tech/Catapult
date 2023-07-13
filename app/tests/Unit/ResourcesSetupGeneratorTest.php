<?php

use UmigameTech\Catapult\FileSystem\CopyDirectoryInterface;
use UmigameTech\Catapult\Generators\ResourcesSetupGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {
    $generator = new ResourcesSetupGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
            'entities' => [],
        ],
        $this->mocked
    );

    $this->result = [];
    $outer = $this;
    $generator->generate(new class($outer) implements CopyDirectoryInterface {
        private $outer;
        public function __construct($outer)
        {
            $this->outer = $outer;
        }
        public function copyDir($source, $dest)
        {
            $this->outer->result = compact('source', 'dest');
        }
    });

    expect($this->result['source'])->toBe('/app/src/Generators/../Templates/resources');
    expect($this->result['dest'])->toBe('/dist/test/resources');
});
