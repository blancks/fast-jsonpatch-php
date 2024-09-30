<?php declare(strict_types=1);

namespace blancks\JsonPatch;

use blancks\JsonPatch\accessors\{
    ArrayAccessorAwareInterface,
    ArrayAccessorAwareTrait,
    ArrayAccessorInterface,
    ArrayAccessor,
    ObjectAccessorAwareInterface,
    ObjectAccessorAwareTrait,
    ObjectAccessorInterface,
    ObjectAccessor
};
use blancks\JsonPatch\exceptions\{
    FastJsonPatchException,
    FastJsonPatchValidationException,
    InvalidPatchException
};
use blancks\JsonPatch\json\{
    JsonHandlerAwareInterface,
    JsonHandlerAwareTrait,
    JsonHandlerInterface,
    BasicJsonHandler
};
use blancks\JsonPatch\operations\{
    PatchOperationInterface,
    PatchValidationTrait,
    Add,
    Copy,
    Move,
    Remove,
    Replace,
    Test
};

/**
 * This class allow to perform a sequence of operations to apply to a target JSON document as per RFC 6902
 * @link https://datatracker.ietf.org/doc/html/rfc6902
 */
final class FastJsonPatch implements
    JsonHandlerAwareInterface,
    ArrayAccessorAwareInterface,
    ObjectAccessorAwareInterface
{
    use JsonHandlerAwareTrait;
    use ArrayAccessorAwareTrait;
    use ObjectAccessorAwareTrait;
    use PatchValidationTrait;

    /**
     * @var mixed reference of the document
     */
    private mixed $document;

    /**
     * @var array<string, PatchOperationInterface> registered classes for handling patch operations
     */
    private array $operations = [];

    public static function fromJson(
        string $document,
        ?JsonHandlerInterface $JsonHandler = null,
        ?ArrayAccessorInterface $ArrayAccessor = null,
        ?ObjectAccessorInterface $ObjectAccessor = null
    ): self {
        $JsonHandler = $JsonHandler ?? new BasicJsonHandler;
        $decodedJson = $JsonHandler->decode($document);
        return new self($decodedJson, $JsonHandler, $ArrayAccessor, $ObjectAccessor);
    }

    public function __construct(
        mixed &$document,
        ?JsonHandlerInterface $JsonHandler = null,
        ?ArrayAccessorInterface $ArrayAccessor = null,
        ?ObjectAccessorInterface $ObjectAccessor = null
    ) {
        $this->document = &$document;
        $this->setArrayAccessor($ArrayAccessor ?? new ArrayAccessor);
        $this->setObjectAccessor($ObjectAccessor ?? new ObjectAccessor);
        $this->setJsonHandler($JsonHandler ?? new BasicJsonHandler);
        $this->registerOperation(new Add);
        $this->registerOperation(new Copy);
        $this->registerOperation(new Move);
        $this->registerOperation(new Remove);
        $this->registerOperation(new Replace);
        $this->registerOperation(new Test);
    }

    /**
     * Allows to register a class that will be responsible to handle a specific patch operation.
     * You can replace a handler class for a given operation or register handlers for custom patch operations
     *
     * @param PatchOperationInterface $PatchOperation
     * @return void
     */
    public function registerOperation(PatchOperationInterface $PatchOperation): void
    {
        if ($PatchOperation instanceof ArrayAccessorAwareInterface) {
            $PatchOperation->setArrayAccessor($this->ArrayAccessor);
        }

        if ($PatchOperation instanceof ObjectAccessorAwareInterface) {
            $PatchOperation->setObjectAccessor($this->ObjectAccessor);
        }

        if ($PatchOperation instanceof JsonHandlerAwareInterface) {
            $PatchOperation->setJsonHandler($this->JsonHandler);
        }

        $this->operations[$PatchOperation->getOperation()] = $PatchOperation;
    }

    /**
     * @param string $patch
     * @return void
     * @throws FastJsonPatchException
     */
    public function apply(string $patch): void
    {
        try {
            $revertPatch = [];
            $document = &$this->document;

            foreach ($this->patchIterator($patch) as $op => $p) {
                $Operation = $this->operations[$op];
                $Operation->validate($p);
                $Operation->apply($document, $p);
                $revertPatch[] = $Operation->getRevertPatch($p);
            }
        } catch (FastJsonPatchException $e) {
            // restore the original document
            foreach (array_reverse($revertPatch) as $p) {
                if (!is_null($p)) {
                    $p = (object) $p;
                    $this->operations[$p->op]->apply($this->document, $p);
                }
            }

            $i = count($revertPatch)-1;

            // validation errors with the patch itself
            if ($e instanceof FastJsonPatchValidationException) {
                throw new InvalidPatchException(
                    sprintf('%s in patch /%d', $e->getMessage(), $i),
                    "/{$i}",
                    $e
                );
            }

            throw $e;
        }
    }

    public function isValidPatch(string $patch): bool
    {
        try {
            foreach ($this->patchIterator($patch) as $op => $p) {
                $this->operations[$op]->validate($p);
            }
            return true;
        } catch (FastJsonPatchException) {
            return false;
        }
    }

    /**
     * @param string $patch
     * @return \Generator & iterable<string, object{op: string, path: string, value?: mixed, from?: string}>
     */
    private function patchIterator(string $patch): \Generator
    {
        $decodedPatch = $this->JsonHandler->decode($patch);

        if (!is_array($decodedPatch)) {
            throw new InvalidPatchException('Invalid patch structure');
        }

        foreach ($decodedPatch as $p) {
            $p = (object) $p;
            $this->assertValidOp($p);
            $this->assertValidPath($p);
            /** @var object{op:string, path: string, value?: mixed, from?: string} $p */
            yield $p->op => $p;
        }
    }
}
