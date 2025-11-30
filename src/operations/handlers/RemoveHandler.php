<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations\handlers;

use blancks\JsonPatch\operations\Remove;

/**
 * @internal
 * @phpstan-import-type TRemoveOperationObject from Remove
 */
final class RemoveHandler extends PatchOperationHandler
{
    private mixed $previous;

    /**
     * @param object&TRemoveOperationObject $patch
     * @return void
     */
    public function validate(object $patch): void
    {
        // op and path are already guaranteed to be part of the patch
        // we only need to validate the additional properties needed for this operation
    }

    /**
     * @param mixed $document
     * @param object&TRemoveOperationObject $patch
     * @return void
     */
    public function apply(mixed &$document, object $patch): void
    {
        $this->previous = $this->JsonHandler->delete($document, $patch->path);
    }

    /**
     * @param object&TRemoveOperationObject $patch
     * @return null|array{
     *     op:string,
     *     path: string,
     *     value?: mixed,
     *     from?: string,
     * }
     */
    public function getRevertPatch(object $patch): ?array
    {
        return ['op' => 'add', 'path' => $patch->path, 'value' => $this->previous];
    }
}
