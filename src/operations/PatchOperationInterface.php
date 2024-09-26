<?php declare(strict_types=1);

namespace blancks\JsonPatch\operations;

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
     * Ensures that $path contains all the necessary data to perform the operation
     *
     * @param object $patch
     * @return void
     */
    public function validate(object $patch): void;

    /**
     * Applies $patch to $document
     *
     * @param mixed $document
     * @param object $patch
     * @return mixed
     */
    public function apply(mixed &$document, object $patch): mixed;
}
