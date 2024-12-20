<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\handlers;

/**
 * Alternative handler that decodes objects in a JSON string as associative arrays instead of \stdClass instances
 */
final class ArrayJsonHandler extends BasicJsonHandler
{
    /**
     * @param string $json
     * @param array{
     *     flags?: int,
     *     depth?: int<1, max>,
     * } $options
     * @return mixed
     */
    public function decode(string $json, array $options = []): mixed
    {
        $options['associative'] = true;
        return parent::decode($json, $options);
    }
}
