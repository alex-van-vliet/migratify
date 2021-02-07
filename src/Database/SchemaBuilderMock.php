<?php


namespace AlexVanVliet\Migratify\Database;


class SchemaBuilderMock
{
    /**
     * @var BlueprintMock[] The list of blueprints.
     */
    protected array $blueprints = [];

    /**
     * Create a table.
     *
     * @param string $table The name of the table.
     * @param callable $cb The callback.
     */
    public function create(string $table, callable $cb)
    {
        if (!array_key_exists($table, $this->blueprints))
            $this->blueprints[$table] = new BlueprintMock($table);
        $cb($this->blueprints[$table]);
    }

    /**
     * Update a table.
     *
     * @param string $table The name of the table.
     * @param callable $cb The callback.
     */
    public function table(string $table, callable $cb)
    {
        if (!array_key_exists($table, $this->blueprints))
            $this->blueprints[$table] = new BlueprintMock($table);
        $cb($this->blueprints[$table]);
    }

    /**
     * @return BlueprintMock[] Get the blueprints
     */
    public function getBlueprints(): array
    {
        return $this->blueprints;
    }
}
