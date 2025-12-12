<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

/**
 * @phpstan-type TMoveOperationObject object{
 *     op:string,
 *     path: string,
 *     from: string,
 * }
 */
final readonly class Move extends PatchOperation
{
    public function __construct(
        public string $path,
        public string $from,
    ) {
        parent::__construct('move');
    }
}
