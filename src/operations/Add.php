<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TAddOperationObject object{
 *     op:string,
 *     path: string,
 *     value: mixed,
 * }
 */
final class Add extends PatchOperation
{
    public function __construct(
        public readonly string $path,
        public readonly mixed $value,
    ) {
        parent::__construct('add');
    }
}
