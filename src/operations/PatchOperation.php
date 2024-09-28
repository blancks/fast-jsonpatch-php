<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\accessors\{
    ArrayAccessorAwareInterface,
    ArrayAccessorAwareTrait,
    ObjectAccessorAwareInterface,
    ObjectAccessorAwareTrait
};
use blancks\JsonPatch\exceptions\{
    ArrayBoundaryException,
    InvalidPatchPathException,
    UnknownPathException
};

abstract class PatchOperation implements
    PatchOperationInterface,
    ArrayAccessorAwareInterface,
    ObjectAccessorAwareInterface
{
    use PatchValidationTrait;
    use ArrayAccessorAwareTrait;
    use ObjectAccessorAwareTrait;

    /**
     * Returns the operation name that the class will handle.
     * Please note that this method will assume the class short name as the name of the operation,
     * feel free to override if this is not the behaviour you want for your operation handler class.
     *
     * @return string
     */
    public function getOperation(): string
    {
        return strtolower((new \ReflectionClass($this))->getShortName());
    }

    /**
     * Returns the $path tokens as array
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6901#section-3
     * @param string $path
     * @return string[]
     */
    protected function pathToTokens(string $path): array
    {
        $tokens = [];

        if ($path !== '') {
            foreach (explode('/', ltrim($path, '/')) as $token) {
                $tokens[] = strtr($token, ['~1' => '/', '~0' => '~']);
            }
        }

        return $tokens;
    }

    protected function documentWriter(mixed &$document, string $path, mixed $value): mixed
    {
        $tokens = $this->pathToTokens($path);
        $pathLength = count($tokens);

        if ($pathLength === 0) {
            $previous = $document;
            $document = $value;
            return $previous;
        }

        $i = 0;
        $lastIndex = $pathLength - 1;

        do {
            $isLastToken = $i === $lastIndex;

            switch (gettype($document)) {
                case 'array':
                    if ($isLastToken) {
                        $isAppendOperation = $tokens[$i] === '-';
                        $count = $this->ArrayAccessor->count($document);
                        $index = $isAppendOperation ? $count : (int) $tokens[$i];

                        // checks for out of bounds for non-empty indexed arrays only
                        if (
                            $count > 0 &&
                            $this->ArrayAccessor->isIndexed($document) &&
                            !$isAppendOperation &&
                            (
                                (string) $index !== $tokens[$i] ||
                                $index < 0 ||
                                $index > $count
                            )
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
                            $previous = $document;
                            $this->ArrayAccessor->set($document, (string) $index, $value);
                            return $previous;
                        }

                        return $this->ArrayAccessor->set($document, (string) $index, $value);
                    }

                    if (!$this->ArrayAccessor->exists($document, $tokens[$i])) {
                        throw new UnknownPathException(
                            sprintf(
                                'Unknown document path "%s"',
                                $path
                            ),
                            $path
                        );
                    }

                    $document = &$this->ArrayAccessor->get($document, $tokens[$i]);
                    break;
                case 'object':
                    if ($isLastToken) {
                        return $this->ObjectAccessor->set($document, $tokens[$i], $value);
                    }

                    if (!$this->ObjectAccessor->exists($document, $tokens[$i])) {
                        throw new UnknownPathException(
                            sprintf('Unknown document path "%s"', $path),
                            $path
                        );
                    }

                    $document = &$this->ObjectAccessor->get($document, $tokens[$i]);
                    break;
                default:
                    throw new UnknownPathException(sprintf('path "%s" does not exists', $path), $path);
            }
        } while (++$i < $pathLength);

        throw new UnknownPathException(sprintf('path "%s" does not exists', $path), $path);
    }

    protected function documentRemover(mixed &$document, string $path): mixed
    {
        $tokens = $this->pathToTokens($path);
        $pathLength = count($tokens);

        if ($pathLength === 0) {
            $previous = $document;
            $document = '';
            return $previous;
        }

        $i = 0;
        $lastIndex = $pathLength - 1;

        do {
            $isLastToken = $i === $lastIndex;

            switch (gettype($document)) {
                case 'array':
                    if ($isLastToken) {
                        return $this->ArrayAccessor->delete($document, $tokens[$i]);
                    }

                    if (!$this->ArrayAccessor->exists($document, $tokens[$i])) {
                        throw new UnknownPathException(
                            sprintf('Unknown document path "%s"', $path),
                            $path
                        );
                    }

                    $document = &$this->ArrayAccessor->get($document, $tokens[$i]);
                    break;
                case 'object':
                    if ($isLastToken) {
                        return $this->ObjectAccessor->delete($document, $tokens[$i]);
                    }

                    if (!$this->ObjectAccessor->exists($document, $tokens[$i])) {
                        throw new UnknownPathException(
                            sprintf('Unknown document path "%s"', $path),
                            $path
                        );
                    }

                    $document = &$this->ObjectAccessor->get($document, $tokens[$i]);
                    break;
                default:
                    throw new UnknownPathException(sprintf('path "%s" does not exists', $path), $path);
            }
        } while (++$i < $pathLength);

        throw new UnknownPathException(sprintf('path "%s" does not exists', $path));
    }

    public function &documentReader(mixed &$document, string $path): mixed
    {
        $tokens = $this->pathToTokens($path);
        $pathLength = count($tokens);

        if ($pathLength === 0) {
            return $document;
        }

        $i = 0;

        do {
            switch (gettype($document)) {
                case 'array':
                    if (!$this->ArrayAccessor->exists($document, $tokens[$i])) {
                        throw new UnknownPathException(
                            sprintf('Unknown document path "%s"', $path),
                            $path
                        );
                    }

                    $document = &$this->ArrayAccessor->get($document, $tokens[$i]);
                    break;
                case 'object':
                    if (!$this->ObjectAccessor->exists($document, $tokens[$i])) {
                        throw new UnknownPathException(
                            sprintf('Unknown document path "%s"', $path),
                            $path
                        );
                    }

                    $document = &$this->ObjectAccessor->get($document, $tokens[$i]);
                    break;
                default:
                    throw new \DomainException(
                        sprintf('trying to access children for item of type "%s"', gettype($document))
                    );
            }
        } while (++$i < $pathLength);

        return $document;
    }
}
