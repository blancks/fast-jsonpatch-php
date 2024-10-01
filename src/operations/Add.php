<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\json\accessors\UndefinedValue;

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
        // op and path are already guaranteed to be part of the patch
        // we only need to validate the additional properties needed for this operation
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
        $this->previous = $this->JsonHandler->write($document, $patch->path, $patch->value);
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
        if ($this->previous instanceof UndefinedValue) {
            return ['op' => 'remove', 'path' => $patch->path];
        }

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
                'path' => substr_replace((string) $patch->path, (string) $this->previous, -1)
            ];
        }

        return ['op' => 'replace', 'path' => $patch->path, 'value' => $this->previous];
    }
}
