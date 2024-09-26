<?php declare(strict_types=1);

namespace blancks\JsonPatch\accessors;

final class ObjectAccessor implements ObjectAccessorInterface
{
    public function exists(object $document, string $key): bool
    {
        return property_exists($document, $key);
    }

    public function &get(object $document, string $key): mixed
    {
        return $document->{$key};
    }

    public function set(object $document, string $key, mixed $value): mixed
    {
        $previous = $document->{$key} ?? null;
        $document->{$key} = $value;
        return $previous;
    }

    public function delete(object $document, string $key): mixed
    {
        $previous = $document->{$key};
        unset($document->{$key});
        return $previous;
    }
}
