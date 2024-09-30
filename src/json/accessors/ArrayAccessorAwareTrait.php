<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\accessors;

trait ArrayAccessorAwareTrait
{
    protected ArrayAccessorInterface $ArrayAccessor;

    public function setArrayAccessor(ArrayAccessorInterface $ArrayAccessor): void
    {
        $this->ArrayAccessor = $ArrayAccessor;
    }
}
