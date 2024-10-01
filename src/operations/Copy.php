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
        $value = $this->JsonHandler->read($document, $patch->from);
        $this->previous = $this->JsonHandler->write($document, $patch->path, $value);
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
        if (str_ends_with($patch->path, '-')) {
            if (!is_int($this->previous)) {
                throw new \LogicException(
                    sprintf(
                        'Return value of array append operation ("-" token) is expected to be '
                            . 'the array size as integer, %s was given instead',
                        gettype($this->previous)
                    )
                );
            }

            return [
                'op' => 'remove',
                'path' => substr_replace($patch->path, (string) $this->previous, -1)
            ];
        }

        return ['op' => 'remove', 'path' => $patch->path];
    }
}
