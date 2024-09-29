<?php declare(strict_types=1);

namespace blancks\JsonPatch\json;

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
