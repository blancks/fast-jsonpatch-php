<?php declare(strict_types=1);

namespace blancks\JsonPatch\accessors;

trait ObjectAccessorAwareTrait
{
    protected ObjectAccessorInterface $ObjectAccessor;

    public function setObjectAccessor(ObjectAccessorInterface $ObjectAccessor): void
    {
        $this->ObjectAccessor = $ObjectAccessor;
    }
}
