<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(UnknownPathException::class)]
final class UnknownPathExceptionTest extends TestCase
{
    #[DataProvider('unknownPathsProvider')]
    public function testOperationsOnUnknownPathShouldFail(string $json, string $patches): void
    {
        $this->expectException(UnknownPathException::class);
        echo FastJsonPatch::apply($json, $patches);
    }

    public static function unknownPathsProvider(): array
    {
        return [
            'Add Object operation on array target should fail' => [
                '["foo", "sil"]',
                '[{"op": "add", "path": "/bar", "value": 42}]'
            ],
            'Add to a bad array index should fail' => [
                '["foo", "sil"]',
                '[{"op": "add", "path": "/bar", "value": "bar"}]'
            ],
            'Copy with bad array index should fail' => [
                '{"baz": [1,2,3], "bar": 1}',
                '[{"op": "copy", "from": "/baz/1e0", "path": "/boo"}]'
            ],
            'Move with bad array index should fail' => [
                '{"foo": 1, "baz": [1,2,3,4]}',
                '[{"op": "move", "from": "/baz/1e0", "path": "/foo"}]'
            ],
            'Remove with bad array index should fail' => [
                '[1, 2, 3, 4]',
                '[{"op": "remove", "path": "/1e0"}]'
            ],
            'Remove existing property with bad array index should fail' => [
                '{"foo": 1, "baz": [{"qux": "hello"}]}',
                '[{"op": "remove", "path": "/baz/1e0/qux"}]'
            ],
            'Replace with bad array index should fail' => [
                '[""]',
                '[{"op": "replace", "path": "/1e0", "value": false}]'
            ],
            'Test against undefined path should fail' => [
                '["foo", "bar"]',
                '[{"op": "test", "path": "/1e0", "value": "bar"}]'
            ],
        ];
    }
}
