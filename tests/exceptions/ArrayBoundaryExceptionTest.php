<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\exceptions;

use blancks\JsonPatch\exceptions\ArrayBoundaryException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessor;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\crud\CrudTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\pointer\JsonPointer6901;
use blancks\JsonPatch\operations\Add;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\FastJsonPatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FastJsonPatch::class)]
#[CoversClass(ArrayBoundaryException::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessor::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(CrudTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(JsonPointer6901::class)]
#[UsesClass(PatchOperation::class)]
#[UsesClass(Add::class)]
final class ArrayBoundaryExceptionTest extends TestCase
{
    /**
     * @param string $json
     * @param string $patch
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    #[DataProvider('outOfBoundsProvider')]
    public function testAddingOutOfArrayBoundariesShouldFail(string $json, string $patch): void
    {
        $this->expectException(ArrayBoundaryException::class);
        $FastJsonPatch = FastJsonPatch::fromJson($json);
        $FastJsonPatch->apply($patch);
    }

    /**
     * @return void
     * @throws \blancks\JsonPatch\exceptions\FastJsonPatchException
     */
    public function testArrayBoundaryExceptionContextPointer(): void
    {
        try {
            $FastJsonPatch = FastJsonPatch::fromJson('{"bar": [1, 2]}');
            $FastJsonPatch->apply('[{"op": "add", "path": "/bar/8", "value": "5"}]');
        } catch (ArrayBoundaryException $e) {
            $this->assertSame('/bar/8', $e->getContextPointer());
        }
    }

    /**
     * @return array<string, string[]>
     */
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
