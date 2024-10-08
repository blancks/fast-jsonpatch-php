<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchValueException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidPatchValueException::class)]
final class InvalidPatchValueExceptionTest extends TestCase
{
    public function testPatchWithMissingValueParameterShouldFail(): void
    {
        $this->expectException(InvalidPatchValueException::class);
        FastJsonPatch::apply('{}', '[{"op":"add", "path": "/foo"}]');
    }

    public function testInvalidPatchValueExceptionContextData(): void
    {
        try {
            FastJsonPatch::apply('{}', '[{"op":"add", "path": "/foo"}]');
        } catch (InvalidPatchValueException $e) {
            $this->assertSame('/0', $e->getContextPointer());
            $this->assertSame('{"op":"add","path":"\/foo"}', $e->getContextDocument());
        }
    }
}
