<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Add extends PatchOperation
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
        $this->previous = $this->documentWriter($document, $patch->path, $patch->value);
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
        if (is_null($this->previous)) {
            return ['op' => 'remove', 'path' => $patch->path];
        }

        if (is_array($this->previous)) {
            return [
                'op' => 'remove',
                'path' => str_ends_with($patch->path, '/-')
                    ? str_replace('/-', '/' . count($this->previous), $patch->path)
                    : $patch->path
            ];
        }

        return ['op' => 'replace', 'path' => $patch->path, 'value' => $this->previous];
    }
}
