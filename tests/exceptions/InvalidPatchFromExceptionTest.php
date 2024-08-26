<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchFromException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidPatchFromException::class)]
final class InvalidPatchFromExceptionTest extends TestCase
{
    public function testPatchWithMissingFromParameterShouldFail(): void
    {
        $this->expectException(InvalidPatchFromException::class);
        FastJsonPatch::apply('{"bar":1}', '[{"op": "copy", "path": "/foo"}]');
    }

    public function testInvalidPatchFromExceptionContextData(): void
    {
        try {
            FastJsonPatch::apply('{"bar":1}', '[{"op": "copy", "path": "/foo"}]');
        } catch (InvalidPatchFromException $e) {
            $this->assertSame('/0', $e->getContextPointer());
            $this->assertSame('{"op":"copy","path":"\/foo"}', $e->getContextDocument());
        }
    }
}
