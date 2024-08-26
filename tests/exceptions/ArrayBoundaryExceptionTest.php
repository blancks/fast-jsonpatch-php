<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\ArrayBoundaryException;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(ArrayBoundaryException::class)]
final class ArrayBoundaryExceptionTest extends TestCase
{
    #[DataProvider('outOfBoundsProvider')]
    public function testAddingOutOfArrayBoundariesShouldFail(string $json, string $patches): void
    {
        $this->expectException(ArrayBoundaryException::class);
        echo FastJsonPatch::apply($json, $patches);
    }

    public static function outOfBoundsProvider(): array
    {
        return [
            'Add to array index with bad number should fail' => [
                '["foo", "sil"]',
                '[{"op": "add", "path": "/1e0", "value": "bar"}]'
            ],
            'Add item out of upper array bounds should fail' => [
                '{"bar": [1, 2]}',
                '[{"op": "add", "path": "/bar/8", "value": "5"}]'
            ],
            'Add item out of lower array bounds should fail' => [
                '{"bar": [1, 2]}',
                '[{"op": "add", "path": "/bar/-1", "value": "5"}]'
            ],
        ];
    }
}
