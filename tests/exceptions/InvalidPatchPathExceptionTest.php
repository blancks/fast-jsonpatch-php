<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchPathException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidPatchPathException::class)]
final class InvalidPatchPathExceptionTest extends TestCase
{
    public function testPatchWithMissingPathParameterShouldFail(): void
    {
        $this->expectException(InvalidPatchPathException::class);
        FastJsonPatch::apply('{}', '[{"op":"add", "value": "bar"}]');
    }
}
