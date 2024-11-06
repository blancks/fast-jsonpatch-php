<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\operations;

use blancks\JsonPatch\exceptions\FastJsonPatchExceptionTrait;
use blancks\JsonPatch\exceptions\InvalidPatchFromException;
use blancks\JsonPatch\exceptions\MalformedPathException;
use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessor;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\crud\CrudTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\handlers\JsonHandlerAwareTrait;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\operations\PatchValidationTrait;
use blancks\JsonPatch\operations\Remove;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Remove::class)]
#[CoversClass(PatchOperation::class)]
#[UsesClass(PatchValidationTrait::class)]
#[UsesClass(CrudTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(JsonHandlerAwareTrait::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessor::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(FastJsonPatchExceptionTrait::class)]
#[UsesClass(InvalidPatchFromException::class)]
#[UsesClass(MalformedPathException::class)]
#[UsesClass(UnknownPathException::class)]
class RemoveTest extends TestCase
{
    private Remove $Operation;

    protected function setUp(): void
    {
        $this->Operation = new Remove();
        $this->Operation->setJsonHandler(new BasicJsonHandler);
    }

    public function testGetOperation(): void
    {
        $this->assertSame('remove', $this->Operation->getOperation());
    }

    public function testValidateMethodWithValidPatch(): void
    {
        $validPatch = (object) ['op' => 'remove', 'path' => '/test/path'];
        $this->Operation->validate($validPatch);
        $this->expectNotToPerformAssertions();
    }

    public function testApply(): void
    {
        $document = ['fruit' => 'apple', 'color' => 'red'];
        $patch = (object) ['op' => 'remove', 'path' => '/color'];
        $this->Operation->apply($document, $patch);
        $this->assertSame(['fruit' => 'apple'], $document);
    }

    public function testApplyWithNonExistingKey(): void
    {
        $this->expectException(UnknownPathException::class);
        $document = ['fruit' => 'apple', 'color' => 'red'];
        $patch = (object) ['op' => 'remove', 'path' => '/nope'];
        $this->Operation->apply($document, $patch);
    }

    public function testGetRevertPatchWithPreviousValue(): void
    {
        $document = ['fruit' => 'apple', 'color' => 'red'];
        $patch = (object) ['op' => 'remove', 'path' => '/color'];
        $this->Operation->apply($document, $patch);
        $revertPatch = $this->Operation->getRevertPatch($patch);
        $this->assertSame(['op' => 'add', 'path' => '/color', 'value' => 'red'], $revertPatch);
    }

    public function testGetRevertPatchWithoutPreviousValue(): void
    {
        $document = ['fruit' => 'apple', 'color' => 'red'];
        $patch = (object) ['op' => 'remove', 'path' => '/flavour'];
        $this->expectException(UnknownPathException::class);
        $this->Operation->apply($document, $patch);
    }
}
