<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Remove extends PatchOperation
{
    public function __construct(
        public readonly string $path,
    ) {
        parent::__construct('remove');
    }
}
