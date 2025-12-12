<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TCopyOperationObject object{
 *     op:string,
 *     path: string,
 *     from: string,
 * }
 */
final readonly class Copy extends PatchOperation
{
    public function __construct(
        public string $path,
        public string $from,
    ) {
        parent::__construct('copy');
    }
}
