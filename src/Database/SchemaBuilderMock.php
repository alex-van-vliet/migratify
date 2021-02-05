<?php


namespace AlexVanVliet\Migratify\Database;


use Illuminate\Database\Schema\Blueprint;

class SchemaBuilderMock
{
    protected array $blueprints = [];

    public function create(string $table, callable $cb)
    {
        if (!array_key_exists($table, $this->blueprints))
            $this->blueprints[$table] = new BlueprintMock($table);
        $cb($this->blueprints[$table]);
    }

    public function table(string $table, callable $cb)
    {
        if (!array_key_exists($table, $this->blueprints))
            $this->blueprints[$table] = new BlueprintMock($table);
        $cb($this->blueprints[$table]);
    }

    public function getBlueprints()
    {
        return $this->blueprints;
    }
}
