<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Move extends PatchOperation
{
    /**
     * @param object{
     *     op:string,
     *     path: string,
     *     from: string,
     * } $patch
     * @return void
     */
    public function validate(object $patch): void
    {
        // op and path are already guaranteed to be part of the patch
        // we only need to validate the additional properties needed for this operation
        $this->assertValidFrom($patch);
    }

    /**
     * @param mixed $document
     * @param object{
     *     op:string,
     *     path: string,
     *     from: string,
     * } $patch
     * @return void
     */
    public function apply(mixed &$document, object $patch): void
    {
        $value = $this->documentRemover($document, $patch->from);
        $this->documentWriter($document, $patch->path, $value);
    }

    /**
     * @param object{
     *     op:string,
     *     path: string,
     *     from: string,
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
        return ['op' => 'move', 'from' => $patch->path, 'path' => $patch->from];
    }
}
