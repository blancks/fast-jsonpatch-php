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
        $tokens = $this->JsonPointerHandler->getTokensFromPointer($path);
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
                    break 2;
            }
        } while (++$i <= $pathLength);

        throw new UnknownPathException(sprintf('path "%s" does not exists', $path), $path);
    }
}
