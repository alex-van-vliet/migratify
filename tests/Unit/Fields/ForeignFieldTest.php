<?php


namespace AlexVanVliet\Migratify\Tests\Unit\Fields;


use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Fields\ForeignField;
use AlexVanVliet\Migratify\Model;
use AlexVanVliet\Migratify\Tests\TestCase;
use Illuminate\Database\Eloquent\Model as BaseModel;

#[Model([
])]
class ForeignFieldTest_UserModel extends BaseModel
{
    protected $table = 'my_table';
}

class ForeignFieldTest extends TestCase
{
    /** @test */
    function foreign_field_is_a_field()
    {
        $this->assertInstanceOf(Field::class, new ForeignField('table_id', ForeignField::FOREIGN_ID,
            ['references' => 'users', 'on' => 'id']));
    }

    /** @test */
    function a_foreign_field_can_reference_a_model()
    {
        $field = new ForeignField('table_id', ForeignField::FOREIGN_ID, [],
            ['references_model' => ForeignFieldTest_UserModel::class]);
        $this->assertSame('my_table', $field->getAttributes()['on']);
        $this->assertSame('id', $field->getAttributes()['references']);
    }
}
