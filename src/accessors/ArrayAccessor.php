<?php declare(strict_types=1);

namespace blancks\JsonPatch\accessors;

final class ArrayAccessor implements ArrayAccessorInterface
{
    /**
     * @param array<string|int, mixed> $document
     * @param string $index
     * @return bool
     */
    public function exists(array &$document, string $index): bool
    {
        return array_key_exists($index, $document);
    }

    /**
     * @param array<string|int, mixed> $document
     * @param string $index
     * @return mixed
     */
    public function &get(array &$document, string $index): mixed
    {
        $value = &$document[$index];
        return $value;
    }

    /**
     * @param array<string|int, mixed> $document
     * @param string $index
     * @param mixed $value
     * @return mixed
     */
    public function set(array &$document, string $index, mixed $value): mixed
    {
        $previous = $document[$index] ?? null;

        if ($this->count($document) > 0 && $this->isIndexed($document)) {
            $type = gettype($value);
            array_splice($document, (int) $index, 0, $type === 'array' || $type === 'object' ? [$value] : $value);
        } else {
            $document[$index] = $value;
        }

        return $previous;
    }

    /**
     * @param array<string|int, mixed> $document
     * @param string $index
     * @return mixed
     */
    public function delete(array &$document, string $index): mixed
    {
        $previous = $document[$index];

        if ($this->isIndexed($document)) {
            array_splice($document, (int) $index, 1);
        } else {
            unset($document[$index]);
        }

        return $previous;
    }

    /**
     * @param array<string|int, mixed> $document
     * @return int
     */
    public function count(array &$document): int
    {
        return count($document);
    }

    /**
     * @param array<string|int, mixed> $document
     * @return bool
     */
    public function isIndexed(array &$document): bool
    {
        return array_is_list($document);
    }
}
