<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\exceptions\FailedTestException;
use blancks\JsonPatch\json\JsonHandlerAwareInterface;
use blancks\JsonPatch\json\JsonHandlerAwareTrait;

final class Test extends PatchOperation implements JsonHandlerAwareInterface
{
    use JsonHandlerAwareTrait;

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
        $item = $this->documentReader($document, $patch->path);

        if (!$this->isJsonEquals($item, $patch->value)) {
            throw new FailedTestException(
                sprintf(
                    'Test operation failed asserting that %s equals %s',
                    $this->JsonHandler->encode($item),
                    $this->JsonHandler->encode($patch->value)
                ),
                $patch->path
            );
        }
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
        return null;
    }

    /**
     * Tells if $a and $b are of the same JSON type
     *
     * @link https://datatracker.ietf.org/doc/html/rfc6902/#section-4.6
     * @param mixed $a
     * @param mixed $b
     * @return bool true if $a and $b are JSON equal, false otherwise
     */
    private function isJsonEquals(mixed $a, mixed $b): bool
    {
        $atype = gettype($a);

        if ($atype === 'array' || $atype === 'object') {
            $a = (array) $a;
            $this->recursiveKeySort($a);
        }

        $btype = gettype($b);

        if ($btype === 'array' || $btype === 'object') {
            $b = (array) $b;
            $this->recursiveKeySort($b);
        }

        return $this->JsonHandler->encode($a) === $this->JsonHandler->encode($b);
    }

    /**
     * Applies ksort to each array element recursively
     *
     * @param array $a
     * @return void
     */
    private function recursiveKeySort(array &$a): void
    {
        foreach ($a as &$item) {
            $type = gettype($item);
            if ($item === 'array' || $type === 'object') {
                $item = (array) $item;
                $this->recursiveKeySort($item);
            }
        }

        ksort($a, SORT_STRING);
    }
}
