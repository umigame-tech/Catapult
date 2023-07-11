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

        if (file_exists("{$projectPath}/tailwind.config.js")) {
            unlink("{$projectPath}/tailwind.config.js");
        }

        exec('npx tailwindcss init -p');
        $content = file_get_contents("{$projectPath}/tailwind.config.js");
        $newContent = preg_replace('/  content: \[\],/', <<<'EOT'
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
  ],
EOT,
            $content
        );

        file_put_contents("{$projectPath}/tailwind.config.js", $newContent);

        $appCssPath = "{$projectPath}/resources/css/app.css";
        $tailwindCssSetup = <<<'EOT'
@tailwind base;
@tailwind components;
@tailwind utilities;
EOT;
        $appCss = file_get_contents($appCssPath);
        if (! str_contains($appCss, $tailwindCssSetup)) {
            file_put_contents($appCssPath, $tailwindCssSetup . "\n" . $appCss);
        }

        if (file_exists("{$projectPath}/vite.config.js")) {
            unlink("{$projectPath}/vite.config.js");
        }
        copy(__DIR__ . '/../Templates/vite.config.js', "{$projectPath}/vite.config.js");

        $viteConfig = file_get_contents("{$projectPath}/vite.config.js");

        $newConfig = preg_replace('/\ +input: \[.*/', <<<'EOT'
            input: [
                './resources/js/app.js',
                './resources/css/app.css',
                './resources/css/sakura.css',
                './resources/css/style.css',
            ],
EOT
        , $viteConfig);

        file_put_contents("{$projectPath}/vite.config.js", $newConfig);

        chdir($currentPath);
    }
}
