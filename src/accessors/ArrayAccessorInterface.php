<?php declare(strict_types=1);

namespace blancks\JsonPatch\accessors;

interface ArrayAccessorInterface
{
    public function exists(array &$document, string $index): bool;

    public function &get(array &$document, string $index): mixed;

    public function set(array &$document, string $index, mixed $value): mixed;

    public function delete(array &$document, string $index): mixed;

    public function count(array &$document): int;

    public function isIndexed(array &$document): bool;
}
