<?php declare(strict_types=1);

namespace blancks\JsonPatch;

use blancks\JsonPatch\exceptions\{
    FastJsonPatchException,
    FastJsonPatchValidationException,
    InvalidPatchException
};
use blancks\JsonPatch\json\handlers\{
    BasicJsonHandler,
    JsonHandlerAwareInterface,
    JsonHandlerAwareTrait,
    JsonHandlerInterface
};
use blancks\JsonPatch\operations\{
    Add,
    Copy,
    Move,
    PatchOperationInterface,
    PatchValidationTrait,
    Remove,
    Replace,
    Test
};

/**
 * This class allow to perform a sequence of operations to apply to a target JSON document as per RFC 6902
 * @link https://datatracker.ietf.org/doc/html/rfc6902
 */
final class FastJsonPatch implements JsonHandlerAwareInterface
{
    use JsonHandlerAwareTrait;
    use PatchValidationTrait;

    /**
     * @var mixed reference of the document
     */
    private mixed $document;

    /**
     * @var array<string, PatchOperationInterface> registered classes for handling patch operations
     */
    private array $operations = [];

    public static function fromJson(string $document, ?JsonHandlerInterface $JsonHandler = null): self {
        $JsonHandler = $JsonHandler ?? new BasicJsonHandler;
        $decodedJson = $JsonHandler->decode($document);
        return new self($decodedJson, $JsonHandler);
    }

    public function __construct(mixed &$document, ?JsonHandlerInterface $JsonHandler = null) {
        $this->document = &$document;
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
    public function registerOperation(PatchOperationInterface $PatchOperation): void {
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
    public function apply(string $patch): void {
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
                    $p = (object)$p;
                    $this->operations[$p->op]->apply($this->document, $p);
                }
            }

            // validation errors with the patch itself
            if ($e instanceof FastJsonPatchValidationException) {
                $i = count($revertPatch);
                throw new InvalidPatchException(
                    sprintf('%s in patch /%d', $e->getMessage(), $i),
                    "/{$i}",
                    $e
                );
            }

            throw $e;
        }
    }

    public function isValidPatch(string $patch): bool {
        try {
            foreach ($this->patchIterator($patch) as $op => $p) {
                $this->operations[$op]->validate($p);
            }
            return true;
        } catch (FastJsonPatchException) {
            return false;
        }
    }

    public function read(string $path): mixed {
        return $this->JsonHandler->read($this->document, $path);
    }

    public function &getDocument(): mixed
    {
        return $this->document;
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
