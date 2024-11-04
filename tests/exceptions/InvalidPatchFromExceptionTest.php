<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchException;
use blancks\JsonPatch\exceptions\InvalidPatchFromException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessor;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\crud\CrudTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\operations\Add;
use blancks\JsonPatch\operations\Copy;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\operations\Remove;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidPatchFromException::class)]
#[UsesClass(InvalidPatchException::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessor::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(CrudTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(PatchOperation::class)]
#[UsesClass(Add::class)]
#[UsesClass(Copy::class)]
#[UsesClass(Remove::class)]
final class InvalidPatchFromExceptionTest extends TestCase
{
    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testPatchWithMissingFromParameterShouldFail(): void
    {
        $this->expectException(InvalidPatchException::class);
        $FastJsonPatch = FastJsonPatch::fromJson('{"bar":1}');
        $FastJsonPatch->apply('[{"op": "copy", "path": "/foo"}]');
    }

    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testInvalidPatchContext(): void
    {
        try {
            $FastJsonPatch = FastJsonPatch::fromJson('{}');
            $FastJsonPatch->apply('[{"op": "add", "path": "/foo", "value": "bar"},{"op": "copy", "path": "/biz"}]');
        } catch (InvalidPatchException $e) {
            $this->assertSame('/1', $e->getContextPointer());
        }
    }
}
