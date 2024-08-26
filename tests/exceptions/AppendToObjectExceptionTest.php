<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\AppendToObjectException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(AppendToObjectException::class)]
final class AppendToObjectExceptionTest extends TestCase
{
    public function testAppendingValueToAnObjectShouldFail(): void
    {
        $this->expectException(AppendToObjectException::class);
        FastJsonPatch::apply('{"foo":"bar"}', '[{"op":"add", "path": "/-", "value":"biz"}]');
    }

    public function testAppendToObjectExceptionContextData(): void
    {
        try {
            FastJsonPatch::apply('{"foo":{"bar":"doh"}}', '[{"op":"add", "path": "/foo/-", "value":"biz"}]');
        } catch (AppendToObjectException $e) {
            $this->assertSame('/foo/-', $e->getContextPointer());
            $this->assertSame('{"bar":"doh"}', $e->getContextDocument());
        }
    }
}
