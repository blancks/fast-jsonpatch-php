<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

final class Copy extends PatchOperation
{
    private mixed $previous;

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
        $this->assertValidOp($patch);
        $this->assertValidPath($patch);
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
        $value = $this->documentReader($document, $patch->from);
        $this->previous = $this->documentWriter($document, $patch->path, $value);
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
        return [
            'op' => 'remove',
            'path' => is_array($this->previous) && str_ends_with($patch->path, '-')
                ? substr_replace($patch->path, (string) count($this->previous), -1)
                : $patch->path
        ];
    }
}
