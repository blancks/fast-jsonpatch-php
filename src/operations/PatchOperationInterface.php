<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

use blancks\JsonPatch\exceptions\FastJsonPatchException;

/**
 * Object implementing this interface should
 * handle the patch application for a specific
 * operation type
 */
interface PatchOperationInterface
{
    /**
     * Must return the operation name that the class will handle
     *
     * @return string
     */
    public function getOperation(): string;

    /**
     * Ensures that $patch contains all the necessary data to perform the operation
     *
     * @param object $patch
     * @return void
     * @throws FastJsonPatchException
     */
    public function validate(object $patch): void;

    /**
     * Applies $patch to $document
     *
     * @param mixed $document
     * @param object $patch
     * @return void
     */
    public function apply(mixed &$document, object $patch): void;

    /**
     * Return the operation needed to revert last patch application
     *
     * @param object{
     *     op:string,
     *     path: string,
     *     value?: mixed,
     *     from?: string,
     * } $patch
     * @return null|array{
     *     op:string,
     *     path: string,
     *     value?: mixed,
     *     from?: string
     * }
     */
    public function getRevertPatch(object $patch): ?array;
}
