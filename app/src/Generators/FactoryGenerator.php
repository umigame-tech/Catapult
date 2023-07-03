<?php

namespace UmigameTech\Catapult\Generators;

class FactoryGenerator extends Generator
{
    private $DatatypeFakerMethodMap = [
        'string' => 'realText',
        'integer' => 'randomNumber',
        'boolean' => 'boolean',
        'date' => 'date',
        'datetime' => 'dateTime',
        'time' => 'time',
        'decimal' => 'randomFloat',
        'text' => 'realText',
    ];

    public function generate($entity)
    {
        $factoryName = implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        )) . 'Factory';

        $fakerList = array_map(
            fn ($attribute) => "'{$attribute['name']}' => \$this->faker->{$this->DatatypeFakerMethodMap[$attribute['type']]}()",
            $entity['attributes']
        );

        $faker = implode(",\n" . $this->indents(3), $fakerList);

        $factory = <<<EOF
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MyGreatEntity>
 */
class {$factoryName} extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            {$faker},
        ];
    }
}

EOF;

        $projectPath = $this->projectPath();
        $factoryPath = "{$projectPath}" . '/database/factories/' . $factoryName . '.php';
        // 既にファイルがある場合は削除してから生成する
        if (file_exists($factoryPath)) {
            unlink($factoryPath);
        }

        file_put_contents($factoryPath, $factory);
    }
}
