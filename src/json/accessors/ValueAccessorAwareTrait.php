<?php declare(strict_types=1);

namespace blancks\JsonPatch\json\accessors;

trait ValueAccessorAwareTrait
{
    protected ValueAccessorInterface $ValueAccessor;

    public function setValueAccessor(ValueAccessorInterface $ValueAccessor): void
    {
        $this->ValueAccessor = $ValueAccessor;
    }
}
