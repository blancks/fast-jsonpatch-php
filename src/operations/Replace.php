<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Replace extends PatchOperation
{
    private mixed $previous;

    /**
     * @param object{
     *     op:string,
     *     path: string,
     *     value: mixed,
     * } $patch
     * @return void
     */
    public function validate(object $patch): void
    {
        $this->assertValidOp($patch);
        $this->assertValidPath($patch);
        $this->assertValidValue($patch);
    }

    /**
     * @param mixed $document
     * @param object{
     *     op:string,
     *     path: string,
     *     value: mixed,
     * } $patch
     * @return void
     */
    public function apply(mixed &$document, object $patch): void
    {
        $this->previous = $this->documentRemover($document, $patch->path);
        $this->documentWriter($document, $patch->path, $patch->value);
    }

    /**
     * @param object{
     *     op:string,
     *     path: string,
     *     value: mixed,
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
        return ['op' => 'replace', 'path' => $patch->path, 'value' => $this->previous];
    }
}
