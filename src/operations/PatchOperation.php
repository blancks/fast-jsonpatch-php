<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

abstract class PatchOperation
{
    public function __construct(
        public readonly string $op,
    ) {}
}
