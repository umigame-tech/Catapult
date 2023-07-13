<?php

use UmigameTech\Catapult\Generators\TailwindCssSetupGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {
    $generator = new TailwindCssSetupGenerator(
        [
            'project_name' => 'test',
            'sealed_prefix' => 'admin',
        ],
        $this->mocked
    );

    $generator->generate();

    expect($this->mocked->contents)
        ->toBeArray()
        ->toHaveLength(3);
});
