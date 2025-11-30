<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Copy extends PatchOperation
{
    public function __construct(
        public readonly string $path,
        public readonly string $from,
    ) {
        parent::__construct('copy');
    }
}
