<?php


namespace AlexVanVliet\Migratify\Fields;


use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

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

    public function __construct(
        protected string $type,
        array $attributes = [],
        protected array $options = [],
    )
    {
        parent::__construct($attributes);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getType()
    {
        return $this->type;
    }

    public function upString(string $name)
    {
        $attributes = $this->getAttributes();
        if (array_key_exists('length', $attributes)) {
            $length = $attributes['length'];
            unset($attributes['length']);
            return ["\$table->string('$name', $length)", $attributes];
        } else {
            return ["\$table->string('$name')", $attributes];
        }
    }

    public function create(string $name)
    {
        $type = Str::ucfirst(Str::camel($this->type));
        if (method_exists($this, "up$type")) {
            [$up, $attributes] = $this->{"up$type"}($name);
        } else {
            $up = "\$table->{$this->type}('$name')";
            $attributes = $this->getAttributes();
        }
        foreach ($attributes as $k => $v) {
            if ($v === true) {
                $up = "{$up}->{$k}()";
            } else if (is_string($v)) {
                $v = addcslashes($v, "\\'");
                $up = "{$up}->{$k}('$v')";
            } else {
                $up = "{$up}->{$k}($v)";
            }
        }
        return [
            $up,
            "\$table->removeColumn('$name')",
        ];
    }

    public function remove(string $name)
    {
        return [
            "\$table->removeColumn('$name')",
            "throw new \Exception('FIXME: add down for removal of column $name')",
        ];
    }

    public function update(string $name, Field $from)
    {
        return [
            "{$this->create($name)[0]}->change()",
            "throw new \Exception('FIXME: add down for update of column $name')",
        ];
    }

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

    public function change()
    {

    }
}
