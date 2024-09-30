<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\accessors;

trait ObjectAccessorAwareTrait
{
    protected ObjectAccessorInterface $ObjectAccessor;

    public function setObjectAccessor(ObjectAccessorInterface $ObjectAccessor): void
    {
        $this->ObjectAccessor = $ObjectAccessor;
    }
}
