<?php declare(strict_types=1);

namespace blancks\JsonPatch\json;

use blancks\JsonPatch\json\JsonHandlerInterface;

interface JsonHandlerAwareInterface
{
    public function setJsonHandler(JsonHandlerInterface $JsonHandler): void;
}
