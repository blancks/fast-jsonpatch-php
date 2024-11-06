<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\operations;

use blancks\JsonPatch\exceptions\FastJsonPatchExceptionTrait;
use blancks\JsonPatch\exceptions\InvalidPatchFromException;
use blancks\JsonPatch\exceptions\InvalidPatchValueException;
use blancks\JsonPatch\exceptions\MalformedPathException;
use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ArrayAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessorAwareTrait;
use blancks\JsonPatch\json\accessors\ValueAccessor;
use blancks\JsonPatch\json\accessors\ValueAccessorAwareTrait;
use blancks\JsonPatch\json\crud\CrudTrait;
use blancks\JsonPatch\json\handlers\BasicJsonHandler;
use blancks\JsonPatch\json\handlers\JsonHandlerAwareTrait;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\operations\PatchValidationTrait;
use blancks\JsonPatch\operations\Replace;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Replace::class)]
#[CoversClass(PatchOperation::class)]
#[UsesClass(PatchValidationTrait::class)]
#[UsesClass(CrudTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(JsonHandlerAwareTrait::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ArrayAccessorAwareTrait::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ObjectAccessorAwareTrait::class)]
#[UsesClass(ValueAccessor::class)]
#[UsesClass(ValueAccessorAwareTrait::class)]
#[UsesClass(FastJsonPatchExceptionTrait::class)]
#[UsesClass(InvalidPatchFromException::class)]
#[UsesClass(MalformedPathException::class)]
#[UsesClass(UnknownPathException::class)]
#[UsesClass(InvalidPatchValueException::class)]
class ReplaceTest extends TestCase
{
    private Replace $Operation;

    protected function setUp(): void
    {
        $this->Operation = new Replace();
        $this->Operation->setJsonHandler(new BasicJsonHandler);
    }

    public function testGetOperation(): void
    {
        $this->assertSame('replace', $this->Operation->getOperation());
    }

    public function testValidPatch(): void
    {
        $this->expectNotToPerformAssertions();
        $patch = (object) ['op' => 'replace', 'path' => '/valid/path', 'value' => 'valid value'];
        $this->Operation->validate($patch);
    }

    public function testInvalidPatchThrowsException(): void
    {
        $this->expectException(InvalidPatchValueException::class);

        /** @var object{op:string, path: string, value: mixed}  $patch */
        $patch = (object) [
            'op' => 'replace',
            'path' => '/valid/path',
            // value is missing
        ];
        $this->Operation->validate($patch);
    }

    public function testApplyToAssociativeDocumentWithValidReplacePatch(): void
    {
        $document = ['simpleKey' => 'simpleValue'];
        $patch = (object) ['op' => 'replace', 'path' => '/simpleKey', 'value' => 'changedValue'];
        $this->Operation->apply($document, $patch);
        $this->assertSame('changedValue', $document['simpleKey']);
    }

    public function testApplyToObjectDocumentWithValidReplacePatch(): void
    {
        $document = (object) ['simpleKey' => 'simpleValue'];
        $patch = (object) ['op' => 'replace', 'path' => '/simpleKey', 'value' => 'changedValue'];
        $this->Operation->apply($document, $patch);
        $this->assertSame('changedValue', $document->simpleKey);
    }

    public function testApplyWithInvalidPathThrowsException(): void
    {
        $this->expectException(UnknownPathException::class);
        $document = [
            'simpleKey' => 'simpleValue',
        ];
        $patch = (object) ['op' => 'replace', 'path' => '/unknown/path', 'value' => 'valueToReplace'];
        $this->Operation->apply($document, $patch);
    }

    public function testGetRevertPatch(): void
    {
        $document = (object) ['simpleKey' => 'simpleValue'];
        $patch = (object) ['op' => 'replace', 'path' => '/simpleKey', 'value' => 'changedValue'];
        $this->Operation->apply($document, $patch);
        $revertedPatch = $this->Operation->getRevertPatch($patch);
        $this->assertSame(['op' => 'replace', 'path' => '/simpleKey', 'value' => 'simpleValue'], $revertedPatch);
    }

    public function testGetRevertPatchWithNullPreviousValue(): void
    {
        $document = (object) ['simpleKey' => null];
        $patch = (object) ['op' => 'replace', 'path' => '/simpleKey', 'value' => 'changedValue'];
        $this->Operation->apply($document, $patch);
        $revertedPatch = $this->Operation->getRevertPatch($patch);
        $this->assertSame(['op' => 'replace', 'path' => '/simpleKey', 'value' => null], $revertedPatch);
    }
}
