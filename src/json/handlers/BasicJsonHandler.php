<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\handlers;

use blancks\JsonPatch\exceptions\MalformedDocumentException;
use blancks\JsonPatch\json\accessors\{
    ArrayAccessor,
    ArrayAccessorAwareInterface,
    ArrayAccessorAwareTrait,
    ArrayAccessorInterface,
    ObjectAccessor,
    ObjectAccessorAwareInterface,
    ObjectAccessorAwareTrait,
    ObjectAccessorInterface,
    ValueAccessor,
    ValueAccessorAwareInterface,
    ValueAccessorAwareTrait,
    ValueAccessorInterface
};
use blancks\JsonPatch\json\crud\CrudTrait;

class BasicJsonHandler implements
    JsonHandlerInterface,
    ArrayAccessorAwareInterface,
    ObjectAccessorAwareInterface,
    ValueAccessorAwareInterface
{
    use ArrayAccessorAwareTrait;
    use ObjectAccessorAwareTrait;
    use ValueAccessorAwareTrait;
    use CrudTrait;

    public function __construct(
        ?ArrayAccessorInterface $ArrayAccessor = null,
        ?ObjectAccessorInterface $ObjectAccessor = null,
        ?ValueAccessorInterface $DataAccessor = null
    ) {
        $this->setArrayAccessor($ArrayAccessor ?? new ArrayAccessor);
        $this->setObjectAccessor($ObjectAccessor ?? new ObjectAccessor);
        $this->setValueAccessor($DataAccessor ?? new ValueAccessor);
    }

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
