<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(UnknownPathException::class)]
final class UnknownPathExceptionTest extends TestCase
{
    /**
     * @param string $json
     * @param string $patch
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    #[DataProvider('unknownPathsProvider')]
    public function testOperationsOnUnknownPathShouldFail(string $json, string $patch): void
    {
        $this->expectException(UnknownPathException::class);
        $FastJsonPatch = FastJsonPatch::fromJson($json);
        $FastJsonPatch->apply($patch);
    }

    /**
     * @param string $json
     * @param string $patch
     * @param string $expectedPointer
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    #[DataProvider('unknownPathsContextProvider')]
    public function testUnknownPathExceptionContextData(
        string $json,
        string $patch,
        string $expectedPointer,
    ): void {
        try {
            $FastJsonPatch = FastJsonPatch::fromJson($json);
            $FastJsonPatch->apply($patch);
        } catch (UnknownPathException $e) {
            $this->assertSame($expectedPointer, $e->getContextPointer());
        }
    }

    /**
     * @return array<string, string[]>
     */
    public static function unknownPathsContextProvider(): array
    {
        return [
            'Invalid object property operation' => [
                '{"foo": 1, "baz": [1,2,3,4]}',
                '[{"op": "add", "path": "/baz/bar/0", "value": "bar"}]',
                '/baz/bar/0',
            ]
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public static function unknownPathsProvider(): array
    {
        return [
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
