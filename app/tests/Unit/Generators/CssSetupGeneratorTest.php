<?php

use UmigameTech\Catapult\Generators\CssSetupGenerator;

beforeEach(function () {
    $this->mocked = mockFileSystems();
});

test('generate', function () {

    $generator = new CssSetupGenerator(
        [
            'project_name' => 'test',
            'entities' => [],
        ],
        $this->mocked
    );

    $result = $generator->generateContent();
    list('content' => $content) = $result;
    expect($content)
        ->toBeString()
        ->toContain('Sakura.css v');
});
