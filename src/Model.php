<?php


namespace AlexVanVliet\Migratify;

use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Fields\Field;
use Attribute;
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
        foreach ($this->fields as $name => $field) {
            assert(count($field) === 1 or count($field) === 2 or count($field) === 3);
            $this->fields[$name] = new Field(...$field);
        }

        if (($this->options['timestamps'] ?? true) === true) {
            $this->fields['created_at'] = new Field(Field::TIMESTAMP, [], ['nullable']);
            $this->fields['updated_at'] = new Field(Field::TIMESTAMP, [], ['nullable']);
        }
        if (($this->options['soft_deletes'] ?? false) === true) {
            $this->fields['deleted_at'] = new Field(Field::TIMESTAMP, [], ['nullable']);
        }
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
     * Convert to blueprint for comparisons.
     *
     * @param string $table The name of the table.
     * @return BlueprintMock
     */
    public function toBlueprint(string $table): BlueprintMock
    {
        $blueprint = new BlueprintMock($table);

        foreach ($this->fields as $name => $field) {
            $stored = $blueprint->{$field->getType()}($name);
            foreach ($field->getAttributes() as $key => $attribute) {
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
