<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TCopyOperationObject object{
 *     op:string,
 *     path: string,
 *     from: string,
 * }
 */
final class Copy extends PatchOperation
{
    public function __construct(
        public readonly string $path,
        public readonly string $from,
    ) {
        parent::__construct('copy');
    }
}
