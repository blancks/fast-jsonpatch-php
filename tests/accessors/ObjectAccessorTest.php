<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\accessors;

use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\UndefinedValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ObjectAccessor::class)]
class ObjectAccessorTest extends TestCase
{
    private ObjectAccessor $ObjectAccessor;

    protected function setUp(): void
    {
        $this->ObjectAccessor = new ObjectAccessor();
    }

    public function testExistsMethodReturnsTrueWhenPropertyExistsInObject(): void
    {
        $obj = new \stdClass();
        $obj->prop = 'value';
        $this->assertTrue($this->ObjectAccessor->exists($obj, 'prop'));
    }

    public function testExistsMethodReturnsFalseWhenPropertyDoesntExistInObject(): void
    {
        $obj = new \stdClass();
        $this->assertFalse($this->ObjectAccessor->exists($obj, 'prop'));
    }

    public function testGetMethodReturnsPropertyWhenPropertyExistsInObject(): void
    {
        $obj = new \stdClass();
        $obj->prop = 'value';
        $this->assertSame('value', $this->ObjectAccessor->get($obj, 'prop'));
    }

    public function testSetMethodReturnsPreviousValueWhenKeyExists(): void
    {
        $obj = new \stdClass();
        $obj->prop = 'old value';
        $previous = $this->ObjectAccessor->set($obj, 'prop', 'new value');
        $this->assertSame('old value', $previous);
    }

    public function testSetMethodReturnsUndefinedValueWhenKeyDoesNotExists(): void
    {
        $obj = new \stdClass();
        $previous = $this->ObjectAccessor->set($obj, 'prop', 'value');
        $this->assertInstanceOf(UndefinedValue::class, $previous);
    }

    public function testDeleteMethodReturnsCorrectPreviousValue(): void
    {
        $obj = new \stdClass();
        $obj->prop = 'value';
        $previous = $this->ObjectAccessor->delete($obj, 'prop');
        $this->assertSame('value', $previous);
    }

    public function testDeleteMethodUnsetObjectProperty(): void
    {
        $obj = new \stdClass();
        $obj->prop = 'value';
        $this->ObjectAccessor->delete($obj, 'prop');
        $this->assertFalse($this->ObjectAccessor->exists($obj, 'prop'));
    }
}
