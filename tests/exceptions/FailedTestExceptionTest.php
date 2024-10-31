<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\FailedTestException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(FailedTestException::class)]
final class FailedTestExceptionTest extends TestCase
{
    /**
     * @param string $json
     * @param string $patch
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    #[DataProvider('failedTestsProvider')]
    public function testOperationsWithFailureCases(string $json, string $patch): void
    {
        $this->expectException(FailedTestException::class);
        $FastJsonPatch = FastJsonPatch::fromJson($json);
        $FastJsonPatch->apply($patch);
    }

    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testFailedTestExceptionContextData(): void
    {
        try {
            $FastJsonPatch = FastJsonPatch::fromJson('{"foo": {"bar": [1, 2, 5, 4]}}');
            $FastJsonPatch->apply('[{"op": "test", "path": "/foo", "value": [1, 2]}]');
        } catch (FailedTestException $e) {
            $this->assertSame('/foo', $e->getContextPointer());
        }
    }

    /**
     * @return array<string, string[]>
     */
    public static function failedTestsProvider(): array
    {
        return [
            'Test null case against non-null value should fail' => [
                '{"foo": "non-null"}',
                '[{"op": "test", "path": "/foo", "value": null}]'
            ],
            'Test string case against null value should fail' => [
                '{"foo": null}',
                '[{"op": "test", "path": "/foo", "value": "non-null"}]'
            ],
            'Test boolean false case against null value should fail' => [
                '{"foo": null}',
                '[{"op": "test", "path": "/foo", "value": false}]'
            ],
            'Test null case against boolean false value should fail' => [
                '{"foo": false}',
                '[{"op": "test", "path": "/foo", "value": null}]'
            ],
            'Test invalid array should fail' => [
                '{"foo": {"bar": [1, 2, 5, 4]}}',
                '[{"op": "test", "path": "/foo", "value": [1, 2]}]'
            ],
            'Test same value with different type should fail' => [
                '{"foo": "1"}',
                '[{"op": "test", "path": "/foo", "value": 1}]'
            ],
        ];
    }
}
