<?php


namespace AlexVanVliet\Migratify;


trait ModelTrait
{
    public function initializeModelTrait()
    {
        $trait = Model::from_attribute(static::class);

        if (!empty($this->fillable))
            throw new InvalidBaseConfigurationException(static::class);
        if (count($this->guarded) !== 1 or $this->guarded[0] !== '*')
            throw new InvalidBaseConfigurationException(static::class);
        [$this->fillable, $this->guarded] = $trait->getFillable();
    }
}
