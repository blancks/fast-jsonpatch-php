<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\json\handlers\JsonHandlerAwareInterface;
use blancks\JsonPatch\json\handlers\JsonHandlerAwareTrait;
use blancks\JsonPatch\json\pointer\JsonPointerHandlerAwareInterface;
use blancks\JsonPatch\json\pointer\JsonPointerHandlerAwareTrait;

abstract class PatchOperation implements
    PatchOperationInterface,
    JsonHandlerAwareInterface,
    JsonPointerHandlerAwareInterface
{
    use PatchValidationTrait;
    use JsonHandlerAwareTrait;
    use JsonPointerHandlerAwareTrait;

    /**
     * Returns the operation name that the class will handle.
     * Please note that this method will assume the class short name as the name of the operation,
     * feel free to override if this is not the behaviour you want for your operation handler class.
     * @return string
     */
    public function getOperation(): string
    {
        return strtolower((new \ReflectionClass($this))->getShortName());
    }
}
