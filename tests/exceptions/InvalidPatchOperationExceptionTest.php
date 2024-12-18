<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchException;
use blancks\JsonPatch\exceptions\InvalidPatchOperationException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\pointer\JsonPointer6901;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidPatchOperationException::class)]
#[UsesClass(InvalidPatchException::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(JsonPointer6901::class)]
#[UsesClass(PatchOperation::class)]
final class InvalidPatchOperationExceptionTest extends TestCase
{
    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testPatchWithMissingOpParameterShouldFail(): void
    {
        $this->expectException(InvalidPatchException::class);
        $FastJsonPatch = FastJsonPatch::fromJson('{}');
        $FastJsonPatch->apply('[{"path": "/foo", "value": "bar"}]');
    }

    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testInvalidPatchOperationExceptionContextData(): void
    {
        try {
            $FastJsonPatch = FastJsonPatch::fromJson('{}');
            $FastJsonPatch->apply('[{"path": "/foo", "value": "bar"}]');
        } catch (InvalidPatchException $e) {
            $this->assertSame('/0', $e->getContextPointer());
        }
    }
}
