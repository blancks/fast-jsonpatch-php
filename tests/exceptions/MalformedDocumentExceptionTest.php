<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\MalformedDocumentException;
use blancks\JsonPatch\FastJsonPatch;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(MalformedDocumentException::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
final class MalformedDocumentExceptionTest extends TestCase
{
    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testMalformedDocumentShouldFail(): void
    {
        $this->expectException(MalformedDocumentException::class);
        $FastJsonPatch = FastJsonPatch::fromJson('{"foo"}');
        $FastJsonPatch->apply('[{"op":"add", "path": "/foo", "value": "bar"}]');
    }

    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testMalformedJsonDocumentContextData(): void
    {
        try {
            $FastJsonPatch = FastJsonPatch::fromJson('{"foo"}');
            $FastJsonPatch->apply('[{"op":"add", "path": "/foo", "value": "bar"}]');
        } catch (MalformedDocumentException $e) {
            $this->assertSame(null, $e->getContextPointer());
        }
    }
}
