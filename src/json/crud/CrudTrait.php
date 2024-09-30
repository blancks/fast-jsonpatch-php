<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\crud;

use blancks\JsonPatch\exceptions\ArrayBoundaryException;
use blancks\JsonPatch\exceptions\UnknownPathException;

trait CrudTrait
{
    /**
     * Returns the $path tokens as array
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6901#section-3
     * @param string $path
     * @return string[]
     */
    private function pathToTokens(string $path): array
    {
        $tokens = [];

        if ($path !== '') {
            foreach (explode('/', ltrim($path, '/')) as $token) {
                $tokens[] = strtr($token, ['~1' => '/', '~0' => '~']);
            }
        }

        return $tokens;
    }

    public function write(mixed &$document, string $path, mixed $value): mixed
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
                        $index = $isAppendOperation ? (string) $count : $tokens[$i];

                        // checks for out of bounds for non-empty indexed arrays only
                        if (
                            $count > 0 &&
                            $this->ArrayAccessor->isIndexed($document) &&
                            !$isAppendOperation &&
                            (
                                (string) intval($index) !== $tokens[$i] ||
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
                            $this->ArrayAccessor->set($document, $index, $value);
                            return $previous;
                        }

                        return $this->ArrayAccessor->set($document, $index, $value);
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

    public function delete(mixed &$document, string $path): mixed
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
                    if (!$this->ArrayAccessor->exists($document, $tokens[$i])) {
                        throw new UnknownPathException(
                            sprintf('Unknown document path "%s"', $path),
                            $path
                        );
                    }

                    if ($isLastToken) {
                        return $this->ArrayAccessor->delete($document, $tokens[$i]);
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

                    if ($isLastToken) {
                        return $this->ObjectAccessor->delete($document, $tokens[$i]);
                    }

                    $document = &$this->ObjectAccessor->get($document, $tokens[$i]);
                    break;
                default:
                    throw new UnknownPathException(sprintf('path "%s" does not exists', $path), $path);
            }
        } while (++$i < $pathLength);

        throw new UnknownPathException(sprintf('path "%s" does not exists', $path));
    }

    public function &read(mixed &$document, string $path): mixed
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
                    throw new UnknownPathException(sprintf('path "%s" does not exists', $path), $path);
            }
        } while (++$i < $pathLength);

        return $document;
    }
}
