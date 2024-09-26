<?php declare(strict_types=1);

namespace blancks\JsonPatch\json;

use blancks\JsonPatch\exceptions\MalformedDocumentException;

class BasicJsonHandler implements JsonHandlerInterface
{
    /**
     * @param mixed $document
     * @param array{
     *     flags?: int,
     *     depth?: int<1, max>,
     * } $options
     * @return string
     */
    public function encode(mixed $document, array $options = []): string
    {
        try {
            return json_encode(
                $document,
                ($options['flags'] ?? 0) | JSON_THROW_ON_ERROR,
                $options['depth'] ?? 512
            );
        } catch (\Throwable $e) {
            throw new MalformedDocumentException('Error while encoding JSON: ' . $e->getMessage(), null, $e);
        }
    }

    /**
     * @param string $json
     * @param array{
     *      associative?: bool,
     *      flags?: int,
     *      depth?: int<1, max>,
     *  } $options
     * @return mixed
     */
    public function decode(string $json, array $options = []): mixed
    {
        try {
            return json_decode(
                $json,
                $options['associative'] ?? false,
                $options['depth'] ?? 512,
                ($options['flags'] ?? 0) | JSON_THROW_ON_ERROR
            );
        } catch (\Exception $e) {
            throw new MalformedDocumentException('Error while decoding JSON: ' . $e->getMessage(), null, $e);
        }
    }
}
