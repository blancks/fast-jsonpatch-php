<?php declare(strict_types=1);

namespace blancks\JsonPatch\accessors;

interface ObjectAccessorInterface
{
    public function exists(object $document, string $key): bool;

    public function &get(object $document, string $key): mixed;

    public function set(object $document, string $key, mixed $value): mixed;

    public function delete(object $document, string $key): mixed;
}
