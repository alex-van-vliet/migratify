<?php


namespace AlexVanVliet\Migratify\Fields;


use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class Field extends Fluent
{
    public const STRING = 'string';
    public const ID = 'id';

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
