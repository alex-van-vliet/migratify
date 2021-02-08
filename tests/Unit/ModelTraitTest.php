<?php


namespace AlexVanVliet\Migratify\Tests\Unit;


use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Model;
use AlexVanVliet\Migratify\ModelTrait;
use AlexVanVliet\Migratify\Tests\TestCase;
use Illuminate\Database\Eloquent\Model as BaseModel;

#[Model([
    'id' => [Field::ID],
    'column' => [Field::STRING],
    'password' => [Field::STRING, [], ['guarded']],
], [
    'timestamps' => false,
])]
class ModelTraitTest_FakeModel extends BaseModel
{
    use ModelTrait;
}

class ModelTraitTest extends TestCase
{
    protected ModelTraitTest_FakeModel $model;

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new ModelTraitTest_FakeModel();
    }

    /** @test */
    function fillable_fields_are_set()
    {
        $this->assertEquals([
            'id',
            'column'
        ], $this->model->getFillable());
    }
}
