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
     * Please note the default implementation takes this from the lowercased class short name, removing any "Handler"
     * suffix.
     * feel free to override if this is not the behaviour you want for your operation handler class.
     * @return string
     */
    public function getOperation(): string
    {
        $name = strtolower((new \ReflectionClass($this))->getShortName());
        if (str_ends_with($name, 'handler')) {
            $name = substr($name, 0, -7);
        }
        return $name;
    }
}
