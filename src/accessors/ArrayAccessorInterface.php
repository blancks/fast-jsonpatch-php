<?php declare(strict_types=1);

namespace blancks\JsonPatch\accessors;

interface ArrayAccessorInterface
{
    /**
     * @param array<string|int, mixed> $document
     * @param string $index
     * @return bool
     */
    public function exists(array &$document, string $index): bool;

    /**
     * @param array<string|int, mixed> $document
     * @param string $index
     * @return mixed
     */
    public function &get(array &$document, string $index): mixed;

    /**
     * @param array<string|int, mixed> $document
     * @param string $index
     * @param mixed $value
     * @return mixed
     */
    public function set(array &$document, string $index, mixed $value): mixed;

    /**
     * @param array<string|int, mixed> $document
     * @param string $index
     * @return mixed
     */
    public function delete(array &$document, string $index): mixed;

    /**
     * @param array<string|int, mixed> $document
     * @return int
     */
    public function count(array &$document): int;

    /**
     * @param array<string|int, mixed> $document
     * @return bool
     */
    public function isIndexed(array &$document): bool;
}
