<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\UnknownPatchOperationException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(UnknownPatchOperationException::class)]
final class UnknownPatchOperationExceptionTest extends TestCase
{
    public function testPatchWithUnknownOpShouldFail(): void
    {
        $this->expectException(UnknownPatchOperationException::class);
        FastJsonPatch::apply('{"foo":"bar"}', '[{"op":"read", "path": "/foo"}]');
    }
}
