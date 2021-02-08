<?php


namespace AlexVanVliet\Migratify\Fields;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * Escape a value to put it in the migration.
 *
 * @param $value
 * @return string
 */
function escape_value($value): string {
    if (is_string($value)) {
        $value = addcslashes($value, "\\'");
        return "'$value'";
    }

    if (is_object($value)) {
        throw new InvalidArgumentException("Objects not supported.");
    }
    if (is_array($value)) {
        throw new InvalidArgumentException("Arrays not supported.");
    }

    if (is_bool($value)) {
        return $value ? 'true' : 'false';
    }

    if (is_null($value)) {
        return 'null';
    }

    return strval($value);
}

class Field extends Fluent
{
    public const ID = 'id';
    public const INCREMENTS = 'increments';
    public const INTEGER_INCREMENTS = 'integerIncrements';
    public const TINY_INCREMENTS = 'tinyIncrements';
    public const SMALL_INCREMENTS = 'smallIncrements';
    public const MEDIUM_INCREMENTS = 'mediumIncrements';
    public const BIG_INCREMENTS = 'bigIncrements';
    public const CHAR = 'char';
    public const STRING = 'string';
    public const TEXT = 'text';
    public const MEDIUM_TEXT = 'mediumText';
    public const LONG_TEXT = 'longText';
    public const INTEGER = 'integer';
    public const TINY_INTEGER = 'tinyInteger';
    public const SMALL_INTEGER = 'smallInteger';
    public const MEDIUM_INTEGER = 'mediumInteger';
    public const BIG_INTEGER = 'bigInteger';
    public const UNSIGNED_INTEGER = 'unsignedInteger';
    public const UNSIGNED_TINY_INTEGER = 'unsignedTinyInteger';
    public const UNSIGNED_SMALL_INTEGER = 'unsignedSmallInteger';
    public const UNSIGNED_MEDIUM_INTEGER = 'unsignedMediumInteger';
    public const UNSIGNED_BIG_INTEGER = 'unsignedBigInteger';
    public const FLOAT = 'float';
    public const DOUBLE = 'double';
    public const DECIMAL = 'decimal';
    public const UNSIGNED_FLOAT = 'unsignedFloat';
    public const UNSIGNED_DOUBLE = 'unsignedDouble';
    public const UNSIGNED_DECIMAL = 'unsignedDecimal';
    public const BOOLEAN = 'boolean';
    public const ENUM = 'enum';
    public const SET = 'set';
    public const JSON = 'json';
    public const JSONB = 'jsonb';
    public const DATE = 'date';
    public const DATE_TIME = 'dateTime';
    public const DATE_TIME_TZ = 'dateTimeTz';
    public const TIME = 'time';
    public const TIME_TZ = 'timeTz';
    public const TIMESTAMP = 'timestamp';
    public const TIMESTAMP_TZ = 'timestampTz';
    public const YEAR = 'year';
    public const BINARY = 'binary';
    public const UUID = 'uuid';
    public const IP_ADDRESS = 'ipAddress';
    public const MAC_ADDRESS = 'macAddress';
    public const GEOMETRY = 'geometry';
    public const POINT = 'point';
    public const LINE_STRING = 'lineString';
    public const POLYGON = 'polygon';
    public const GEOMETRY_COLLECTION = 'geometryCollection';
    public const MULTI_POINT = 'multiPoint';
    public const MULTI_LINE_STRING = 'multiLineString';
    public const MULTI_POLYGON = 'multiPolygon';
    public const MULTI_POLYGON_Z = 'multiPolygonZ';

    /**
     * Field constructor.
     *
     * @param string $type The type of the field.
     * @param array $attributes Its attributes.
     * @param array $options The options.
     */
    public function __construct(
        protected string $type,
        array $attributes = [],
        protected array $options = [],
    )
    {
        parent::__construct($attributes);
    }

    /**
     * Get the options of the field.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the type of the field.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the line which would create the field.
     *
     * @param string $name The name of the column.
     * @return string
     * @throws ReflectionException
     */
    public function getUpLine(string $name): string
    {
        $attributes = $this->getAttributes();

        // Get the parameters of the function
        $reflection = new ReflectionClass(Blueprint::class);
        $method = $reflection->getMethod($this->type);
        $parameters = $method->getParameters();
        assert(count($parameters) > 0);
        assert($parameters[0]->getName() === 'column');
        array_shift($parameters);

        // Set the parameters of the function
        $up = "\$table->{$this->type}('$name'";
        foreach ($parameters as $parameter) {
            if (array_key_exists($parameter->getName(), $attributes)) {
                $value = escape_value($attributes[$parameter->getName()]);
                unset($attributes[$parameter->getName()]);
            } else if (in_array($parameter->getName(), $attributes)) {
                $key = array_search($parameter->getName(), $attributes);
                if (is_int($key)) {
                    $value = escape_value(true);
                    unset($attributes[$key]);
                }
            } else {
                $value = escape_value($parameter->getDefaultValue());
            }
            $up = "$up, {$value}";
        }
        $up = "$up)";

        // Set other attributes (nullable, ...)
        foreach ($attributes as $k => $v) {
            if (is_int($k)) {
                $up = "{$up}->{$v}()";
            } else {
                $v = escape_value($v);
                $up = "{$up}->{$k}($v)";
            }
        }
        return $up;
    }

    /**
     * Get the line which would remove the field.
     *
     * @param string $name The name of the column.
     * @return string
     */
    public function getDownLine(string $name): string
    {
        return "\$table->removeColumn('$name')";
    }

    /**
     * Get the lines to up/down the creation of the field.
     *
     * @param string $name The name of the column.
     * @return string[]
     * @throws ReflectionException
     */
    public function create(string $name): array
    {
        return [
            $this->getUpLine($name),
            $this->getDownLine($name),
        ];
    }

    /**
     * Get the lines to up/down the removal of the field.
     *
     * @param string $name
     * @return string[]
     */
    public function remove(string $name): array
    {
        return [
            $this->getDownLine($name),
            "throw new \Exception('FIXME: add down for removal of column $name')",
        ];
    }

    /**
     * Get the lines to up/down the update of the field.
     *
     * @param string $name The name of the column.
     * @param Field $from The initial type of the field.
     * @return string[]
     * @throws ReflectionException
     */
    public function update(string $name, Field $from): array
    {
        return [
            "{$this->getUpLine($name)}->change()",
            "throw new \Exception('FIXME: add down for update of column $name')",
        ];
    }

    /**
     * Check whether two fields are equal.
     *
     * @param Field $other The other field.
     * @return bool
     */
    public function equals(Field $other)
    {
        if ($this->type !== $other->type)
            return false;

        if (count($this->getAttributes()) !== count($other->getAttributes()))
            return false;

        foreach ($this->getAttributes() as $k => $v) {
            if (!array_key_exists($k, $other->getAttributes()))
                return false;
            if ($v != $other->getAttributes()[$k])
                return false;
        }

        return true;
    }

    /**
     * Disable the change function in the migration.
     */
    public function change()
    {

    }
}
