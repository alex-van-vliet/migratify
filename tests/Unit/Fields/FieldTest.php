<?php


namespace AlexVanVliet\Migratify\Tests\Unit\Fields;


use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Model;
use AlexVanVliet\Migratify\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use InvalidArgumentException;
use ReflectionClass;
use function AlexVanVliet\Migratify\Fields\escape_value;

class FieldTest extends TestCase
{
    /** @test */
    function escape_value_escapes_string()
    {
        $this->assertEquals("'this\\'s a test'", escape_value('this\'s a test'));
    }

    /** @test */
    function escape_value_escapes_null()
    {
        $this->assertEquals("null", escape_value(null));
    }

    /** @test */
    function escape_value_escapes_booleans()
    {
        $this->assertEquals('true', escape_value(true));
        $this->assertEquals('false', escape_value(false));
    }

    /** @test */
    function escape_value_keeps_integers()
    {
        $this->assertEquals('123', escape_value(123));
        $this->assertEquals('-99', escape_value(-99));
    }

    /** @test */
    function escape_value_keeps_floats()
    {
        $this->assertEquals('120.3456789', escape_value(120.3456789));
        $this->assertEquals('-9999999', escape_value(-9999999));
    }

    /** @test */
    function escape_value_throws_on_array()
    {
        $this->expectException(InvalidArgumentException::class);

        escape_value([]);
    }

    /** @test */
    function escape_value_throws_on_object()
    {
        $this->expectException(InvalidArgumentException::class);

        escape_value(new class {
        });
    }

    /** @test */
    function all_fields_type_exist()
    {
        $fieldReflectionClass = new ReflectionClass(Field::class);
        $constants = $fieldReflectionClass->getConstants();

        $blueprintReflectionClass = new ReflectionClass(Blueprint::class);
        foreach ($constants as $constant) {
            $this->assertNotNull($blueprintReflectionClass->getMethod($constant));
        }
    }

    /** @test */
    function get_options_returns_the_options()
    {
        $field = new Field(Field::STRING, [], ['my_option']);
        $this->assertEquals(['my_option'], $field->getOptions());
    }

    /** @test */
    function get_type_returns_the_type()
    {
        $field = new Field(Field::STRING);
        $this->assertEquals('string', $field->getType());
    }

    /** @test */
    function get_up_line_adds_the_column()
    {
        $field = new Field(Field::TEXT);
        $this->assertEquals("\$table->text('column')", $field->getUpLine('column'));
    }

    /** @test */
    function get_up_line_fills_arguments_provided_with_associative_array()
    {
        $field = new Field(Field::STRING, ['length' => 255]);
        $this->assertEquals("\$table->string('column', 255)", $field->getUpLine('column'));
    }

    /** @test */
    function get_up_line_fills_arguments_with_normal_array()
    {
        $field = new Field(Field::INTEGER, ['unsigned', 'autoIncrement']);
        $this->assertEquals("\$table->integer('column', true, true)", $field->getUpLine('column'));
    }

    /** @test */
    function get_up_line_fills_arguments_with_default()
    {
        $field = new Field(Field::INTEGER);
        $this->assertEquals("\$table->integer('column', false, false)", $field->getUpLine('column'));
    }

    /** @test */
    function get_up_line_fills_arguments_mixes_defaults_and_provided()
    {
        $field = new Field(Field::INTEGER, ['unsigned']);
        $this->assertEquals("\$table->integer('column', false, true)", $field->getUpLine('column'));
    }

    /** @test */
    function get_down_line_removes_the_column()
    {
        $field = new Field(Field::INTEGER);
        $this->assertEquals("\$table->removeColumn('column')", $field->getDownLine('column'));
    }

    /** @test */
    function create_gets_the_up_and_down_lines()
    {
        $field = new Field(Field::TEXT);
        $this->assertEquals([
            "\$table->text('column')",
            "\$table->removeColumn('column')",
        ], $field->create('column'));
    }

    /** @test */
    function remove_gets_the_down_and_exception_lines()
    {
        $field = new Field(Field::TEXT);
        $this->assertEquals([
            "\$table->removeColumn('column')",
            "throw new \Exception('FIXME: add down for removal of column column')",
        ], $field->remove('column'));
    }

    /** @test */
    function update_gets_the_change_and_exception_lines()
    {
        $field = new Field(Field::TEXT);
        $this->assertEquals([
            "\$table->text('column')->change()",
            "throw new \Exception('FIXME: add down for update of column column')",
        ], $field->update('column', new Field(Field::INTEGER)));
    }

    /** @test */
    function equals_is_true_for_same_fields()
    {
        $field1 = new Field(Field::STRING, ['length' => 255], ['guarded']);
        $field2 = new Field(Field::STRING, ['length' => 255], ['guarded']);

        $this->assertTrue($field1->equals($field2));
    }

    /** @test */
    function equals_is_false_for_different_types()
    {
        $field1 = new Field(Field::STRING, ['length' => 255], ['guarded']);
        $field2 = new Field(Field::CHAR, ['length' => 255], ['guarded']);

        $this->assertFalse($field1->equals($field2));
    }

    /** @test */
    function equals_is_false_for_different_attributes()
    {
        $field1 = new Field(Field::STRING, ['length' => 255], ['guarded']);
        $field2 = new Field(Field::STRING, ['length' => 128], ['guarded']);

        $this->assertFalse($field1->equals($field2));
    }

    /** @test */
    function equals_is_true_for_different_options()
    {
        $field1 = new Field(Field::STRING, ['length' => 255], ['guarded']);
        $field2 = new Field(Field::STRING, ['length' => 255], ['my_option']);

        $this->assertTrue($field1->equals($field2));
    }

    /** @test */
    function its_model_can_be_set()
    {
        $model = new Model([]);
        $field = new Field(Field::STRING, model: $model);

        $this->assertEquals($model, $field->getModel());
    }
}
