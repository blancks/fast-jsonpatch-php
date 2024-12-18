<?php declare(strict_types=1);

namespace blancks\JsonPatch;

use blancks\JsonPatch\exceptions\{
    FastJsonPatchException,
    FastJsonPatchValidationException,
    InvalidPatchException,
    InvalidPatchOperationException,
    UnknownPathException
};
use blancks\JsonPatch\json\handlers\{
    BasicJsonHandler,
    JsonHandlerAwareInterface,
    JsonHandlerAwareTrait,
    JsonHandlerInterface
};
use blancks\JsonPatch\json\pointer\{
    JsonPointer6901,
    JsonPointerHandlerAwareInterface,
    JsonPointerHandlerAwareTrait,
    JsonPointerHandlerInterface
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
final class FastJsonPatch implements JsonHandlerAwareInterface, JsonPointerHandlerAwareInterface
{
    use JsonPointerHandlerAwareTrait;
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

    /**
     * Creates a FastJsonPatch instance from a json string document
     * @param string $document
     * @param JsonHandlerInterface|null $JsonHandler handler responsible for handling encoding/decoding and crud operations against the document
     * @param JsonPointerHandlerInterface|null $JsonPointerHandler
     * @return self
     */
    public static function fromJson(
        string $document,
        ?JsonHandlerInterface $JsonHandler = null,
        ?JsonPointerHandlerInterface $JsonPointerHandler = null
    ): self {
        $JsonHandler = $JsonHandler ?? new BasicJsonHandler;
        $JsonPointerHandler = $JsonPointerHandler ?? new JsonPointer6901;
        $decodedJson = $JsonHandler->decode($document);
        return new self($decodedJson, $JsonHandler, $JsonPointerHandler);
    }

    /**
     * Construct the class to perform patch against the given $document reference
     * @param mixed $document
     * @param JsonHandlerInterface|null $JsonHandler handler responsible for handling encoding/decoding and crud operations against the document
     * @param JsonPointerHandlerInterface|null $JsonPointerHandler
     */
    public function __construct(
        mixed &$document,
        ?JsonHandlerInterface $JsonHandler = null,
        ?JsonPointerHandlerInterface $JsonPointerHandler = null
    ) {
        $this->document = &$document;
        $JsonHandler = $JsonHandler ?? new BasicJsonHandler;
        $JsonPointerHandler = $JsonPointerHandler ?? new JsonPointer6901;

        if ($JsonHandler instanceof JsonPointerHandlerAwareInterface) {
            $JsonHandler->setJsonPointerHandler($JsonPointerHandler);
        }

        $this->setJsonPointerHandler($JsonPointerHandler);
        $this->setJsonHandler($JsonHandler);
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
     * @param PatchOperationInterface $PatchOperation
     * @return void
     */
    public function registerOperation(PatchOperationInterface $PatchOperation): void
    {
        if ($PatchOperation instanceof JsonHandlerAwareInterface) {
            $PatchOperation->setJsonHandler($this->JsonHandler);
        }

        if ($PatchOperation instanceof JsonPointerHandlerAwareInterface) {
            $PatchOperation->setJsonPointerHandler($this->JsonPointerHandler);
        }

        $this->operations[$PatchOperation->getOperation()] = $PatchOperation;
    }

    /**
     * Applies the patch to the referenced document.
     * The operation is atomic, if the patch cannot be applied the original document is restored
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
                if (!isset($this->operations[$op])) {
                    throw new InvalidPatchOperationException(sprintf('Unknown operation "%s"', $op));
                }
                $Operation = $this->operations[$op];
                $Operation->validate($p);
                $Operation->apply($document, $p);
                $revertPatch[] = $Operation->getRevertPatch($p);
            }
        } catch (FastJsonPatchException $e) {
            foreach (array_reverse($revertPatch) as $p) {
                if (!is_null($p)) {
                    $p = (object) $p;
                    $this->operations[$p->op]->apply($this->document, $p);
                }
            }

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

    /**
     * Tells if the json patch is syntactically valid
     * @param string $patch
     * @return bool
     */
    public function isValidPatch(string $patch): bool
    {
        try {
            foreach ($this->patchIterator($patch) as $op => $p) {
                if (!isset($this->operations[$op])) {
                    return false;
                }
                $this->operations[$op]->validate($p);
            }
            return true;
        } catch (FastJsonPatchException) {
            return false;
        }
    }

    /**
     * Uses a JSON Pointer (RFC-6901) to fetch data from the referenced document
     * @param string $path the json pointer
     * @return mixed
     * @throws UnknownPathException if the pointer does not match to a valid path
     */
    public function read(string $path): mixed
    {
        return $this->JsonHandler->read($this->document, $path);
    }

    /**
     * Returns the document reference that the instance is holding
     * @return mixed
     */
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
