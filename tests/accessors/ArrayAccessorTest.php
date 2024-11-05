<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\accessors;

use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\UndefinedValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArrayAccessor::class)]
class ArrayAccessorTest extends TestCase
{
    private ArrayAccessor $ArrayAccessor;

    protected function setUp(): void
    {
        $this->ArrayAccessor = new ArrayAccessor();
    }

    public function testExists(): void
    {
        $array = ['key' => 'value'];
        $this->assertTrue($this->ArrayAccessor->exists($array, 'key'));
        $this->assertFalse($this->ArrayAccessor->exists($array, 'missing_key'));
    }

    public function testGet(): void
    {
        $array = ['key' => 'value'];
        $this->assertSame('value', $this->ArrayAccessor->get($array, 'key'));
    }

    public function testSetWithStringKey(): void
    {
        $array = ['key' => 'value'];
        $oldValue = $this->ArrayAccessor->set($array, 'new_key', 'new_value');
        $this->assertInstanceOf(UndefinedValue::class, $oldValue);
        $this->assertSame('new_value', $array['new_key']);
    }

    public function testSetWithNumericalIndex(): void
    {
        $array = [];
        $oldValue = $this->ArrayAccessor->set($array, '0', 'new_value');
        $this->assertInstanceOf(UndefinedValue::class, $oldValue);
        $this->assertSame('new_value', $array[0]);
    }

    public function testSetReplaceWithStringKey(): void
    {
        $array = ['key' => 'value'];
        $oldValue = $this->ArrayAccessor->set($array, 'key', 'new_value');
        $this->assertSame('value', $oldValue);
        $this->assertSame('new_value', $array['key']);
    }

    public function testSetReplaceWithNumericalIndex(): void
    {
        $array = ['value'];
        $oldValue = $this->ArrayAccessor->set($array, '0', 'new_value');
        $this->assertSame('value', $oldValue);
        $this->assertSame('new_value', $array[0]);
    }

    public function testDeleteWithStringKey(): void
    {
        $array = ['key' => 'value', 'key_to_delete' => 'value_to_delete'];
        $oldValue = $this->ArrayAccessor->delete($array, 'key_to_delete');
        $this->assertSame('value_to_delete', $oldValue);
        $this->assertArrayNotHasKey('key_to_delete', $array);
    }

    public function testDeleteWithNumericalIndex(): void
    {
        $array = ['value', 'value_to_delete'];
        $oldValue = $this->ArrayAccessor->delete($array, '1');
        $this->assertSame('value_to_delete', $oldValue);
        $this->assertArrayNotHasKey('1', $array);
    }

    public function testCountEmptyArray(): void
    {
        $array = [];
        $this->assertSame(0, $this->ArrayAccessor->count($array));
    }

    public function testCountNonEmptyArray(): void
    {
        $array = ['key' => 'value', 'another_key' => 'another_value'];
        $this->assertSame(2, $this->ArrayAccessor->count($array));
    }

    public function testCountAfterAddingElements(): void
    {
        $array = [];
        $this->ArrayAccessor->set($array, 'key', 'value');
        $this->ArrayAccessor->set($array, 'another_key', 'another_value');
        $this->assertSame(2, $this->ArrayAccessor->count($array));
    }

    public function testCountAfterDeletingElements(): void
    {
        $array = ['key' => 'value', 'another_key' => 'another_value'];
        $this->ArrayAccessor->delete($array, 'key');
        $this->assertSame(1, $this->ArrayAccessor->count($array));
    }

    public function testIsIndexedWithIndexedArray(): void
    {
        $array = [1, 2, 3];
        $this->assertTrue($this->ArrayAccessor->isIndexed($array));
    }

    public function testIsIndexedWithAssociativeArray(): void
    {
        $array = ['key' => 'value', 'another_key' => 'another_value'];
        $this->assertFalse($this->ArrayAccessor->isIndexed($array));
    }

    public function testIsIndexWithMixedArray(): void
    {
        $array = [1, 2, 'key' => 'value', 'another_key' => 'another_value'];
        $this->assertFalse($this->ArrayAccessor->isIndexed($array));
    }

    public function testIsIndexedWithEmptyArray(): void
    {
        $array = [];
        $this->assertTrue($this->ArrayAccessor->isIndexed($array));
    }
}
