<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchOperationException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidPatchOperationException::class)]
final class InvalidPatchOperationExceptionTest extends TestCase
{
    public function testPatchWithMissingOpParameterShouldFail(): void
    {
        $this->expectException(InvalidPatchOperationException::class);
        FastJsonPatch::apply('{}', '[{"path": "/foo", "value": "bar"}]');
    }
}
