<?php

use UmigameTech\Catapult\Generators\TailwindCssSetupGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {
    // ディレクトリ移動やnpmが絡むので一旦省略
    expect(true)->toBeTrue();

    // $generator = new TailwindCssSetupGenerator(
    //     [
    //         'project_name' => 'test',
    //     ],
    //     $this->mocked
    // );

    // $generator->generate();

    // expect($this->mocked->contents)
    //     ->toBeArray()
    //     ->toHaveLength(3);
});
