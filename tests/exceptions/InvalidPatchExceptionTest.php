<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidPatchException::class)]
final class InvalidPatchExceptionTest extends TestCase
{
    public function testInvalidPatchShouldFail(): void
    {
        $this->expectException(InvalidPatchException::class);
        FastJsonPatch::apply('{}', '{"op":"add", "path": "/foo"}');
    }
}
