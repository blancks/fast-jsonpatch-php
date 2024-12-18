<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\operations;

use blancks\JsonPatch\exceptions\FastJsonPatchExceptionTrait;
use blancks\JsonPatch\exceptions\InvalidPatchValueException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessor;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\crud\CrudTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\handlers\JsonHandlerAwareTrait;
use blancks\JsonPatch\json\pointer\JsonPointer6901;
use blancks\JsonPatch\operations\Add;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\operations\PatchValidationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Add::class)]
#[CoversClass(PatchOperation::class)]
#[UsesClass(PatchValidationTrait::class)]
#[UsesClass(CrudTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(JsonPointer6901::class)]
#[UsesClass(JsonHandlerAwareTrait::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessor::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(FastJsonPatchExceptionTrait::class)]
#[UsesClass(InvalidPatchValueException::class)]
class AddTest extends TestCase
{
    private Add $Operation;

    protected function setUp(): void
    {
        $this->Operation = new Add();
        $this->Operation->setJsonHandler(new BasicJsonHandler);
    }

    public function testGetOperation(): void
    {
        $this->assertSame('add', $this->Operation->getOperation());
    }

    public function testValidate(): void
    {
        $this->expectNotToPerformAssertions();

        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/test', 'value' => 'value'];
        $this->Operation->validate($patch);
    }

    public function testValidateThrowsExceptionForMissingValue(): void
    {
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/test'];

        $this->expectException(InvalidPatchValueException::class);
        $this->Operation->validate($patch);
    }

    public function testApply(): void
    {
        $document = (object) ['test' => 'oldvalue'];
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/test', 'value' => 'newvalue'];
        $this->Operation->apply($document, $patch);
        $this->assertEquals('newvalue', $document->test);
    }

    public function testApplyAddsNewProperty(): void
    {
        $document = (object) [];
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/newprop', 'value' => 'newvalue'];
        $this->Operation->apply($document, $patch);
        $this->assertEquals('newvalue', $document->newprop);
    }

    public function testApplyAddsUpdatesProperty(): void
    {
        $document = (object) ['key' => 'foo'];
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/key', 'value' => 'bar'];
        $this->Operation->apply($document, $patch);
        $this->assertEquals('bar', $document->key);
    }

    public function testGetRevertPatchForUndefinedPrevious(): void
    {
        $document = (object) ['test' => 'oldvalue'];
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/newprop', 'value' => 'newvalue'];

        // Apply the patch to set the previous value
        $this->Operation->apply($document, $patch);
        $revertPatch = $this->Operation->getRevertPatch($patch);

        $this->assertEquals(['op' => 'remove', 'path' => $patch->path], $revertPatch);
    }

    public function testGetRevertPatchForDefinedPrevious(): void
    {
        $document = (object) ['key' => 'foo'];
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/key', 'value' => 'bar'];

        // Apply the patch to set the previous value
        $this->Operation->apply($document, $patch);
        $revertPatch = $this->Operation->getRevertPatch($patch);

        // The previous value of the 'key' field was 'foo'
        $this->assertEquals(['op' => 'replace', 'path' => $patch->path, 'value' => 'foo'], $revertPatch);
    }

    public function testGetRevertPatchForArrayAppend(): void
    {
        $document = (object) ['list' => ['item1', 'item2']];
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/list/-', 'value' => 'item3'];

        // Apply the patch to set the previous value
        $this->Operation->apply($document, $patch);
        $revertPatch = $this->Operation->getRevertPatch($patch);

        // The last index of the list before the patch was 1
        $this->assertEquals(['op' => 'remove', 'path' => '/list/2'], $revertPatch);
    }

    /**
     * The provided code defines a test case that verifies the behavior of the getRevertPatch method
     * when handling an array append operation that is not properly handled by the JsonHandler.
     * By setting up the JsonHandler mock to return an unexpected value, the test ensures that the
     * getRevertPatch method behaves correctly by throwing a \LogicException.
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRevertPatchThrowsExceptionWhenJsonHandlerReturnsUnexpectedValueForArrayAppend(): void
    {
        $JsonHandlerMock = $this->createMock(BasicJsonHandler::class);
        $JsonHandlerMock->method('write')->willReturn('unexpected-value');
        $this->Operation->setJsonHandler($JsonHandlerMock);

        $document = (object) ['list' => ['item1', 'item2']];
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) ['op' => 'add', 'path' => '/list/-', 'value' => 'item3'];

        // Apply the patch to set the previous value
        $this->Operation->apply($document, $patch);

        $this->expectException(\LogicException::class);
        $this->Operation->getRevertPatch($patch);
    }
}
