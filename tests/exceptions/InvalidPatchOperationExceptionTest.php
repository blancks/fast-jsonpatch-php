<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidPatchOperationException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
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

    public function testInvalidPatchOperationExceptionContextData(): void
    {
        try {
            FastJsonPatch::apply('{}', '[{"path": "/foo", "value": "bar"}]');
        } catch (InvalidPatchOperationException $e) {
            $this->assertSame('/0', $e->getContextPointer());
            $this->assertSame('{"path":"\/foo","value":"bar"}', $e->getContextDocument());
        }
    }
}
