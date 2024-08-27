<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\InvalidJsonDepthException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(InvalidJsonDepthException::class)]
final class InvalidJsonDepthExceptionTest extends TestCase
{
    public function testInvalidJsonDepthShouldFail(): void
    {
        // @codeCoverageIgnore
        $this->expectException(InvalidJsonDepthException::class);
        FastJsonPatch::apply('{}', '[{"op": "add", "path": "/foo", "value": "Hello World"}]', -1);
    }

    public function testInvalidJsonDepthinDocumentToStringShouldFail(): void
    {
        $FastJsonPatch = new FastJsonPatch;
        $reflection = new \ReflectionClass($FastJsonPatch);
        $method = $reflection->getMethod('documentToString');
        $method->setAccessible(true);

        $this->expectException(InvalidJsonDepthException::class);
        $method->invoke($FastJsonPatch, [], 0, -1);
    }
}
