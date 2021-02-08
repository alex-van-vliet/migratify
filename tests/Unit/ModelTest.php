<?php


namespace AlexVanVliet\Migratify\Tests\Unit;


use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Model;
use AlexVanVliet\Migratify\Tests\TestCase;
use Illuminate\Database\Eloquent\Model as BaseModel;

#[Model([])]
class ModelTest_FakeModel extends BaseModel
{
}

class ModelTest extends TestCase
{
    /** @test */
    function the_fields_are_initialized()
    {
        $model = new Model([
            'column' => [Field::TEXT],
        ]);

        $this->assertArrayHasKey('column', $model->getFields());
        $this->assertEquals(new Field(Field::TEXT), $model->getFields()['column']);
    }

    /** @test */
    function timestamps_option_creates_a_created_at_field()
    {
        $model = new Model([]);

        $this->assertArrayHasKey('created_at', $model->getFields());
        $this->assertEquals(new Field(Field::TIMESTAMP, ['nullable']), $model->getFields()['created_at']);
    }

    /** @test */
    function timestamps_option_creates_an_updated_at_field()
    {
        $model = new Model([]);

        $this->assertArrayHasKey('updated_at', $model->getFields());
        $this->assertEquals(new Field(Field::TIMESTAMP, ['nullable']), $model->getFields()['updated_at']);
    }

    /** @test */
    function timestamps_disabled_does_not_create_fields()
    {
        $model = new Model([], ['timestamps' => false]);

        $this->assertArrayNotHasKey('created_at', $model->getFields());
        $this->assertArrayNotHasKey('updated_at', $model->getFields());
    }

    /** @test */
    function soft_deletes_disabled_does_not_create_fields()
    {
        $model = new Model([]);

        $this->assertArrayNotHasKey('deleted_at', $model->getFields());
    }

    /** @test */
    function soft_deletes_creates_a_deleted_at_field()
    {
        $model = new Model([], ['soft_deletes' => true]);

        $this->assertArrayHasKey('deleted_at', $model->getFields());
        $this->assertEquals(new Field(Field::TIMESTAMP, ['nullable']), $model->getFields()['created_at']);
    }

    /** @test */
    function the_model_can_be_retrieved_from_an_attribute()
    {
        $model = Model::from_attribute(ModelTest_FakeModel::class);

        $this->assertEquals(new Model([]), $model);
    }

    /** @test */
    function the_model_can_be_converted_to_a_mock_blueprint()
    {
        $model = new Model(['id' => [Field::ID], 'column' => [Field::STRING, ['length' => 128, 'nullable']]], ['timestamps' => false]);

        $expected = new BlueprintMock('table', function ($table) {
            $table->id('id');
            $table->string('column', 128)->nullable();
        });

        $this->assertEquals($expected, $model->toBlueprint('table'));
    }

    /** @test */
    function the_fillable_fills_can_be_retrieved()
    {
        $model = new Model(['fillable' => [Field::TEXT], 'guarded' => [Field::TEXT, [], ['guarded']]], ['timestamps' => false]);

        $this->assertEquals([['fillable'], ['guarded']], $model->getFillable());
    }
}
