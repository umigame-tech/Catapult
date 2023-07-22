<?php

namespace UmigameTech\Catapult\Generators;

class TailwindCssSetupGenerator extends Generator
{
    public function generate()
    {
        $currentPath = getcwd();

        $projectPath = $this->projectPath();

        chdir($projectPath);
        exec('npm install -D tailwindcss postcss autoprefixer');

        $twPath = "{$projectPath}/tailwind.config.js";
        if ($this->checker->exists($twPath)) {
            $this->remover->remove($twPath);
        }

        exec('npx tailwindcss init -p');
        $content = $this->reader->read($twPath);
        $newContent = preg_replace('/  content: \[\],/', <<<'EOT'
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
  ],
  corePlugins: {
    preflight: false,
  },
EOT,
            $content
        );

        $this->writer->write(
            path: "{$projectPath}/tailwind.config.js",
            content: $newContent
        );

        $appCssPath = "{$projectPath}/resources/css/app.css";
        $tailwindCssSetup = <<<'EOT'
@tailwind base;
@tailwind components;
@tailwind utilities;
EOT;
        $appCss = $this->reader->read($appCssPath);
        if (! str_contains($appCss, $tailwindCssSetup)) {
            $this->writer->write(
                path: $appCssPath,
                content: $tailwindCssSetup . "\n" . $appCss
            );
        }

        $vitePath = "{$projectPath}/vite.config.js";
        if ($this->checker->exists($vitePath)) {
            $this->remover->remove($vitePath);
        }

        $this->copier->copyFile(
            source: __DIR__ . '/../Templates/vite.config.js',
            dest: $vitePath
        );

        $viteConfig = $this->reader->read($vitePath);

        $newConfig = preg_replace('/\ +input: \[.*/', <<<'EOT'
            input: [
                './resources/js/app.js',
                './resources/css/sakura.css',
                './resources/css/app.css',
                './resources/css/style.css',
            ],
EOT
        , $viteConfig);

        $this->writer->write(
            path: $vitePath,
            content: $newConfig
        );

        chdir($currentPath);
    }
}
