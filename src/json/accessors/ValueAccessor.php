<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\accessors;

use blancks\JsonPatch\exceptions\ArrayBoundaryException;
use blancks\JsonPatch\exceptions\UnknownPathException;

class ValueAccessor implements ValueAccessorInterface
{
    public function write(
        ArrayAccessorInterface|ObjectAccessorInterface $Accessor,
        mixed &$document,
        string $path,
        ?string $token,
        mixed $value
    ): mixed {
        if ($token === null) {
            $previous = $document;
            $document = $value;
            return $previous;
        }

        if ($Accessor instanceof ObjectAccessorInterface) {
            /** @var object $document */
            return $Accessor->set($document, $token, $value);
        }

        $isAppendOperation = $token === '-';
        $count = $Accessor->count($document);
        $index = $isAppendOperation ? (string) $count : $token;
        $isOutOfBounds = (string) intval($index) !== $token || $index < 0 || $index > $count;

        // checks for out of bounds for non-empty indexed arrays only
        if (
            $count > 0 &&
            !$isAppendOperation &&
            $Accessor->isIndexed($document) &&
            $isOutOfBounds
        ) {
            throw new ArrayBoundaryException(
                sprintf(
                    'Exceeding array boundaries trying to add index "%s',
                    $index
                ),
                $path
            );
        }

        if ($isAppendOperation) {
            $Accessor->set($document, $index, $value);
            return $count;
        }

        return $Accessor->set($document, $index, $value);
    }

    public function &read(
        ArrayAccessorInterface|ObjectAccessorInterface $Accessor,
        mixed &$document,
        string $path,
        ?string $token
    ): mixed {
        if ($token === null) {
            return $document;
        }

        if (!$Accessor->exists($document, $token)) {
            throw new UnknownPathException(
                sprintf('Unknown document path "%s"', $path),
                $path
            );
        }

        return $Accessor->get($document, $token);
    }

    public function delete(
        ArrayAccessorInterface|ObjectAccessorInterface $Accessor,
        mixed &$document,
        string $path,
        ?string $token
    ): mixed {
        if ($token === null) {
            $previous = $document;
            $document = '';
            return $previous;
        }

        if (!$Accessor->exists($document, $token)) {
            throw new UnknownPathException(
                sprintf('Unknown document path "%s"', $path),
                $path
            );
        }

        return $Accessor->delete($document, $token);
    }
}
