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
    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testMalformedDocumentShouldFail(): void
    {
        $this->expectException(MalformedDocumentException::class);
        $FastJsonPatch = FastJsonPatch::fromJson('{"foo"}');
        $FastJsonPatch->apply('[{"op":"add", "path": "/foo", "value": "bar"}]');
    }

    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testMalformedJsonDocumentContextData(): void
    {
        try {
            $FastJsonPatch = FastJsonPatch::fromJson('{"foo"}');
            $FastJsonPatch->apply('[{"op":"add", "path": "/foo", "value": "bar"}]');
        } catch (MalformedDocumentException $e) {
            $this->assertSame(null, $e->getContextPointer());
        }
    }
}
