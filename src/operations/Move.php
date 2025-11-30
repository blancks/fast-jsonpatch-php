<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TMoveOperationObject object{
 *     op:string,
 *     path: string,
 *     from: string,
 * }
 */
final class Move extends PatchOperation
{
    public function __construct(
        public readonly string $path,
        public readonly string $from,
    ) {
        parent::__construct('move');
    }
}
