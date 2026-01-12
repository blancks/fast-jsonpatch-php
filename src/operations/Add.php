<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TAddOperationObject object{
 *     op:string,
 *     path: string,
 *     value: mixed,
 * }
 */
final readonly class Add extends PatchOperation
{
    public function __construct(
        public string $path,
        public mixed $value,
    ) {
        parent::__construct('add');
    }
}
