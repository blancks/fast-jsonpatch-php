<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchException;
use blancks\JsonPatch\exceptions\InvalidPatchPathException;
use blancks\JsonPatch\FastJsonPatch;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\operations\PatchOperation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidPatchPathException::class)]
#[UsesClass(InvalidPatchException::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(PatchOperation::class)]
final class InvalidPatchPathExceptionTest extends TestCase
{
    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testPatchWithMissingPathParameterShouldFail(): void
    {
        $this->expectException(InvalidPatchException::class);
        $FastJsonPatch = FastJsonPatch::fromJson('{}');
        $FastJsonPatch->apply('[{"op":"add", "value": "bar"}]');
    }

    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testInvalidPatchPathExceptionContextData(): void
    {
        try {
            $FastJsonPatch = FastJsonPatch::fromJson('{}');
            $FastJsonPatch->apply('[{"op":"add", "value": "bar"}]');
        } catch (InvalidPatchException $e) {
            $this->assertSame('/0', $e->getContextPointer());
        }
    }
}
