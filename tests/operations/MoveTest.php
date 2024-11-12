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
use blancks\JsonPatch\operations\Move;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\operations\PatchValidationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Move::class)]
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
class MoveTest extends TestCase
{
    private Move $Operation;

    protected function setUp(): void
    {
        $this->Operation = new Move();
        $this->Operation->setJsonHandler(new BasicJsonHandler);
    }

    public function testGetOperation(): void
    {
        $this->assertSame('move', $this->Operation->getOperation());
    }

    public function testValidateWithValidPatchObject(): void
    {
        $patch = (object) ['op' => 'move', 'path' => '/path/to/resource', 'from' => '/path/from/resource'];
        $this->Operation->validate($patch);
        $this->assertTrue(true);
    }

    public function testValidateWithPatchObjectMissingFrom(): void
    {
        $this->expectException(InvalidPatchFromException::class);
        /** @var object{op:string, path: string, from: string} $patch */
        $patch = (object) ['op' => 'move', 'path' => '/path/to/resource'];
        $this->Operation->validate($patch);
    }

    public function testValidateWithPatchObjectHavingInvalidFrom(): void
    {
        $this->expectException(MalformedPathException::class);
        $patch = (object) ['op' => 'move', 'path' => '/path/to/resource', 'from' => 'invalid/from/path'];
        $this->Operation->validate($patch);
    }

    public function testApplyWithValidPatchObject(): void
    {
        $document = (object) [
            'path' => (object) ['to' => (object) ['resource' => 'value1']],
            'path1' => (object) ['from' => (object) ['resource' => 'value2']]
        ];
        $patch = (object) ['op' => 'move', 'path' => '/path/to/resource', 'from' => '/path1/from/resource'];
        $this->Operation->apply($document, $patch);
        $this->assertEquals('value2', $document->path->to->resource);
    }

    public function testApplyWithPatchObjectMissingFrom(): void
    {
        $this->expectException(UnknownPathException::class);
        $document = (object) ['path' => ['to' => ['resource' => 'value1']]];
        $patch = (object) ['op' => 'move', 'path' => '/path/to/resource', 'from' => '/path1/from/resource'];
        $this->Operation->apply($document, $patch);
    }

    public function testApplyWithPatchObjectHavingNonExistentFrom(): void
    {
        $this->expectException(UnknownPathException::class);
        $document = (object) ['path' => ['to' => ['resource' => 'value1']]];
        $patch = (object) ['op' => 'move', 'path' => '/path/to/resource', 'from' => '/invalid/from/path'];
        $this->Operation->apply($document, $patch);
    }

    public function testGetRevertPatch(): void
    {
        $patch = (object) ['op' => 'move', 'path' => '/path/to/resource', 'from' => '/path/from/resource'];
        $revertedPatch = $this->Operation->getRevertPatch($patch);
        $expectedRevertedPatch = ['op' => 'move', 'from' => '/path/to/resource', 'path' => '/path/from/resource'];
        $this->assertEquals($expectedRevertedPatch, $revertedPatch);
    }
}
