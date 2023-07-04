<?php

namespace UmigameTech\Catapult\Generators;

use UmigameTech\Catapult\Datatypes\AttributeType;

class FactoryGenerator extends Generator
{
    private function attributeTypeMap(string $type): string
    {
        return match ($type) {
            AttributeType::String->value => 'realText',
            AttributeType::Username->value => 'userName',
            AttributeType::Email->value => 'email',
            AttributeType::Tel->value => 'phoneNumber',
            AttributeType::Integer->value => 'randomNumber',
            AttributeType::Boolean->value => 'boolean',
            AttributeType::Date->value => 'date',
            AttributeType::Datetime->value => 'dateTime',
            AttributeType::Time->value => 'time',
            AttributeType::Decimal->value => 'randomFloat',
            AttributeType::Text->value => 'realText',
            default => throw new \Exception('Invalid attribute type'),
        };
    }

    public function generate($entity)
    {
        $factoryName = implode('', array_map(
            fn ($word) => ucfirst($word),
            explode('_', $entity['name'])
        )) . 'Factory';

        $fakerList = array_map(
            fn ($attribute) => "'{$attribute['name']}' => \$this->faker->{$this->attributeTypeMap($attribute['type'])}()",
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
