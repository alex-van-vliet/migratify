<?php


namespace AlexVanVliet\Migratify;

use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Fields\Field;
use Attribute;
use Illuminate\Database\Schema\Blueprint;
use ReflectionClass;
use ReflectionException;

#[Attribute]
class Model
{
    /**
     * Model constructor.
     *
     * @param array $fields The fields of the model.
     * @param array $options The options.
     */
    public function __construct(
        protected array $fields,
        protected array $options = [],
    )
    {
        $instantiatedFields = [];
        $this->options = array_merge([
            'id' => true,
            'timestamps' => true,
            'soft_deletes' => false,
        ], $this->options);

        if ($this->options['id']) {
            $instantiatedFields['id'] = new Field(Field::ID, [], ['guarded']);
        }
        if ($this->options['timestamps']) {
            $instantiatedFields['created_at'] = new Field(Field::TIMESTAMP, ['nullable'], []);
            $instantiatedFields['updated_at'] = new Field(Field::TIMESTAMP, ['nullable'], []);
        }
        if ($this->options['soft_deletes']) {
            $instantiatedFields['deleted_at'] = new Field(Field::TIMESTAMP, ['nullable'], []);
        }

        foreach ($this->fields as $name => $field) {
            assert(count($field) === 1 or count($field) === 2 or count($field) === 3);
            $instantiatedFields[$name] = new Field(...$field);
        }

        $this->fields = $instantiatedFields;
    }

    /**
     * Get the model from the attribute.
     *
     * @param string $model The model class.
     * @return static
     * @throws ModelNotFoundException
     * @throws ReflectionException
     */
    public static function from_attribute(string $model): self
    {
        $reflectionClass = new ReflectionClass($model);

        $attributes = $reflectionClass->getAttributes(self::class);
        if (empty($attributes))
            throw new ModelNotFoundException($reflectionClass->getName());
        assert(count($attributes) == 1);

        return $attributes[0]->newInstance();
    }

    /**
     * Get all the fields.
     *
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Get all the options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Convert to blueprint for comparisons.
     *
     * @param string $table The name of the table.
     * @return BlueprintMock
     */
    public function toBlueprint(string $table): BlueprintMock
    {
        $blueprint = new BlueprintMock($table);

        foreach ($this->fields as $name => $field) {
            $attributes = $field->getAttributes();

            $reflection = new ReflectionClass(BlueprintMock::class);
            $method = $reflection->getMethod($field->getType());
            $parameters = $method->getParameters();
            assert(count($parameters) > 0);
            assert($parameters[0]->getName() === 'column');
            $arguments = [$name];
            array_shift($parameters);

            foreach ($parameters as $parameter) {
                if (array_key_exists($parameter->getName(), $attributes)) {
                    $arguments[] = $attributes[$parameter->getName()];
                    unset($attributes[$parameter->getName()]);
                } else if (in_array($parameter->getName(), $attributes)) {
                    $key = array_search($parameter->getName(), $attributes);
                    if (is_int($key)) {
                        $arguments[] = true;
                        unset($attributes[$key]);
                    }
                } else {
                    $arguments[] = $parameter->getDefaultValue();
                }
            }

            $stored = $method->invokeArgs($blueprint, $arguments);
            foreach ($attributes as $key => $attribute) {
                if (is_int($key)) {
                    $stored->{$attribute}();
                } else {
                    $stored->{$key}($attribute);
                }
            }
        }

        return $blueprint;
    }

    /**
     * Get the list of fillable and guarded fields.
     *
     * @return string[][]
     */
    public function getFillable()
    {
        $fillable = [];
        $guarded = [];
        foreach ($this->fields as $name => $field) {
            if (in_array('guarded', $field->getOptions())) {
                $guarded[] = $name;
            } else {
                $fillable[] = $name;
            }
        }
        return [$fillable, $guarded];
    }
}
