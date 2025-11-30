<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TReplaceOperationObject object{
 *     op:string,
 *     path: string,
 *     value: mixed,
 * }
 */
final class Replace extends PatchOperation
{
    public function __construct(
        public readonly string $path,
        public readonly mixed $value,
    ) {
        parent::__construct('replace');
    }
}
