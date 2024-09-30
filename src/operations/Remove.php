<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Remove extends PatchOperation
{
    private mixed $previous;

    /**
     * @param object{
     *     op:string,
     *     path: string,
     * } $patch
     * @return void
     */
    public function validate(object $patch): void
    {
        // op and path are already guaranteed to be part of the patch
        // we only need to validate the additional properties needed for this operation
    }

    /**
     * @param mixed $document
     * @param object{
     *     op:string,
     *     path: string,
     * } $patch
     * @return void
     */
    public function apply(mixed &$document, object $patch): void
    {
        $this->previous = $this->JsonHandler->delete($document, $patch->path);
    }

    /**
     * @param object{
     *     op:string,
     *     path: string,
     * } $patch
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
