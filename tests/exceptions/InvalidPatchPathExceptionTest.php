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

    public function testInvalidPatchPathExceptionContextData(): void
    {
        try {
            FastJsonPatch::apply('{}', '[{"op":"add", "value": "bar"}]');
        } catch (InvalidPatchPathException $e) {
            $this->assertSame('/0', $e->getContextPointer());
            $this->assertSame('{"op":"add","value":"bar"}', $e->getContextDocument());
        }
    }
}
