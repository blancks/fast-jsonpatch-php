<?php declare(strict_types=1);

namespace blancks\JsonPatchTest;

use blancks\JsonPatch\exceptions\InvalidPatchPathException;
use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(FastJsonPatch::class)]
#[UsesClass(UnknownPathException::class)]
#[UsesClass(InvalidPatchPathException::class)]
final class FastJsonPatchTest extends JsonPatchCompliance
{
    public function testValidatePatch(): void
    {
        $this->expectNotToPerformAssertions();
        FastJsonPatch::validatePatch('[{"op": "add", "path": "/foo", "value": "Hello World"}]');
    }

    public function testValidatePatchShouldFail(): void
    {
        // @codeCoverageIgnore
        $this->expectException(InvalidPatchPathException::class);
        FastJsonPatch::validatePatch('[{"op": "add", "value": "Hello World"}]');
    }

    public function testParsePath(): void
    {
        $json = '[{"foo":[{"bar":"hello world"}]}]';
        $this->assertSame('hello world', FastJsonPatch::parsePath($json, '/0/foo/0/bar'));
    }

    public function testRemoveFromAssociativeObject(): void
    {
        $json = '{"foo": false}';
        $patch = '[{"op": "remove", "path": "/foo"}]';
        $this->assertSame([], FastJsonPatch::applyDecode($json, $patch, true));
    }

    #[DataProvider('atomicOperationsProvider')]
    public function testAtomicOperations(string $json, string $patches, string $expected): void
    {
        $document = json_decode($json);
        $patch = json_decode($patches);

        try {
            FastJsonPatch::applyByReference($document, $patch);
        } catch (\Throwable) {
            // expecting some error
        }

        $this->assertSame(
            $this->normalizeJson($expected),
            $this->normalizeJson(json_encode($document))
        );
    }

    #[DataProvider('validOperationsProvider')]
    public function testValidJsonPatches(string $json, string $patches, string $expected): void
    {
        $this->assertSame(
            $this->normalizeJson($expected),
            $this->normalizeJson(FastJsonPatch::apply($json, $patches))
        );
    }
}
