<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

abstract readonly class PatchOperation
{
    public function __construct(
        public string $op,
    ) {}
}
