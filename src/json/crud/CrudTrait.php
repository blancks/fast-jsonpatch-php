<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\crud;

use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\json\accessors\ArrayAccessorInterface;
use blancks\JsonPatch\json\accessors\ObjectAccessorInterface;

trait CrudTrait
{
    public function write(mixed &$document, string $path, mixed $value): mixed
    {
        $item = $this->pathResolver($document, $path);
        return $this->ValueAccessor->write(
            $item['Accessor'],
            $item['document'],
            $path,
            $item['token'],
            $value
        );
    }

    public function &read(mixed &$document, string $path): mixed
    {
        $item = $this->pathResolver($document, $path);
        return $this->ValueAccessor->read(
            $item['Accessor'],
            $item['document'],
            $path,
            $item['token']
        );
    }

    public function update(mixed &$document, string $path, mixed $value): mixed
    {
        $item = $this->pathResolver($document, $path);
        $previous = $this->ValueAccessor->delete(
            $item['Accessor'],
            $item['document'],
            $path,
            $item['token'],
        );
        $this->ValueAccessor->write(
            $item['Accessor'],
            $item['document'],
            $path,
            $item['token'],
            $value
        );
        return $previous;
    }

    public function delete(mixed &$document, string $path): mixed
    {
        $itemData = $this->pathResolver($document, $path);
        return $this->ValueAccessor->delete(
            $itemData['Accessor'],
            $itemData['document'],
            $path,
            $itemData['token']
        );
    }

    /**
     * Explores the document based on the given JSON Pointer and returns the last item,
     * the last token and the proper accessor to perform CRUD operations on target data
     * @param mixed $document
     * @param string $path
     * @return array{
     *     'Accessor': ArrayAccessorInterface|ObjectAccessorInterface,
     *     'document': mixed,
     *     'token': ?string,
     * }
     */
    private function pathResolver(mixed &$document, string $path): array
    {
        $tokens = $this->pathToTokens($path);
        $pathLength = count($tokens);

        if ($pathLength === 0) {
            return [
                'Accessor' => gettype($document) === 'array' ? $this->ArrayAccessor : $this->ObjectAccessor,
                'document' => &$document,
                'token' => null,
            ];
        }

        $i = 0;
        --$pathLength;

        do {
            switch (gettype($document)) {
                case 'array':
                    if ($i === $pathLength) {
                        return [
                            'Accessor' => $this->ArrayAccessor,
                            'document' => &$document,
                            'token' => $tokens[$i],
                        ];
                    }

                    $document = &$this->ArrayAccessor->get($document, $tokens[$i]);
                    break;
                case 'object':
                    if ($i === $pathLength) {
                        return [
                            'Accessor' => $this->ObjectAccessor,
                            'document' => &$document,
                            'token' => $tokens[$i],
                        ];
                    }

                    $document = &$this->ObjectAccessor->get($document, $tokens[$i]);
                    break;
                default:
                    throw new UnknownPathException(sprintf('path "%s" does not exists', $path), $path);
            }
        } while (++$i <= $pathLength);

        throw new \LogicException(sprintf('Unexpected failure occurred while exploring path "%s"', $path));
    }

    /**
     * Returns the given JSON Pointer (RFC-6901) as an array of tokens
     * @link https://datatracker.ietf.org/doc/html/rfc6901#section-3
     * @param string $path the JSON Pointer
     * @return string[]
     */
    private function pathToTokens(string $path): array
    {
        if ($path !== '') {
            $path = strtr(substr($path, 1), ['/' => '~ ', '~1' => '/', '~0' => '~']);
            return explode('~ ', $path);
        }

        return [];
    }
}
