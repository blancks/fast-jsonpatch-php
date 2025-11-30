<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TRemoveOperationObject object{
 *     op:string,
 *     path: string,
 * }
 */
final class Remove extends PatchOperation
{
    public function __construct(
        public readonly string $path,
    ) {
        parent::__construct('remove');
    }
}
