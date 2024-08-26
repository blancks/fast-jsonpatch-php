<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\MalformedPathException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(MalformedPathException::class)]
final class MalformedPathExceptionTest extends TestCase
{
    public function testPatchWithMalformedPathParameterShouldFail(): void
    {
        $this->expectException(MalformedPathException::class);
        FastJsonPatch::apply('{}', '[{"op":"add", "path": "foo", "value": "bar"}]');
    }

    public function testMalformedPathExceptionContextData(): void
    {
        try {
            FastJsonPatch::apply('{}', '[{"op":"add", "path": "foo", "value": "bar"}]');
        } catch (MalformedPathException $e) {
            $this->assertSame('foo', $e->getContextPointer());
        }
    }
}
