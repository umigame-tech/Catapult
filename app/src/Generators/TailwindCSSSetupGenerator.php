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
        exec('npx tailwindcss init -p');
        $content = file_get_contents("{$projectPath}/tailwind.config.js");
        $newContent = preg_replace('/  content: \[\],/', <<<'EOT'
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
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

        chdir($currentPath);
    }
}
