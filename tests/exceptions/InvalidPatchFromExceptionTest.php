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
}
