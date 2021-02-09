<?php


namespace AlexVanVliet\Migratify\Tests\Unit\Fields;


use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Fields\ForeignField;
use AlexVanVliet\Migratify\Tests\TestCase;

class ForeignFieldTest extends TestCase
{
    /** @test */
    function foreign_field_is_a_field()
    {
        $this->assertInstanceOf(Field::class, new ForeignField('table_id', ForeignField::FOREIGN_ID));
    }
}
