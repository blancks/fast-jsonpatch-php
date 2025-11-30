<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TTestOperationObject object{
 *     op:string,
 *     path: string,
 *     value: mixed,
 * }
 */
final class Test extends PatchOperation
{
    public function __construct(
        public readonly string $path,
        public readonly mixed $value,
    ) {
        parent::__construct('test');
    }
}
