<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\MalformedDocumentException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(MalformedDocumentException::class)]
final class MalformedDocumentExceptionTest extends TestCase
{
    public function testMalformedDocumentShouldFail(): void
    {
        $this->expectException(MalformedDocumentException::class);
        FastJsonPatch::apply('{"foo"}', '[{"op":"add", "path": "/foo", "value": "bar"}]');
    }

    public function testMalformedJsonDocumentContextData(): void
    {
        try {
            FastJsonPatch::apply('{"foo"}', '[{"op":"add", "path": "/foo", "value": "bar"}]');
        } catch (MalformedDocumentException $e) {
            $this->assertSame(null, $e->getContextPointer());
            $this->assertSame('{"foo"}', $e->getContextDocument());
        }
    }

    public function testInvalidRootOfJsonDocumentContextData(): void
    {
        try {
            FastJsonPatch::apply('"foo"', '[{"op":"add", "path": "/foo", "value": "bar"}]');
        } catch (MalformedDocumentException $e) {
            $this->assertSame(null, $e->getContextPointer());
            $this->assertSame('"foo"', $e->getContextDocument());
        }
    }

    public function testMalformedDocumentToStringShouldFail(): void
    {
        $FastJsonPatch = new FastJsonPatch;
        $reflection = new \ReflectionClass($FastJsonPatch);
        $method = $reflection->getMethod('documentToString');

        $this->expectException(MalformedDocumentException::class);
        $method->invoke($FastJsonPatch, [mb_convert_encoding('§', 'ISO-8859-1', 'UTF-8')]);  // invalid UTF-8
    }
}
