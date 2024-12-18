<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\operations;

use blancks\JsonPatch\exceptions\FastJsonPatchExceptionTrait;
use blancks\JsonPatch\exceptions\InvalidPatchFromException;
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
use blancks\JsonPatch\json\pointer\JsonPointer6901;
use blancks\JsonPatch\operations\Copy;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\operations\PatchValidationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Copy::class)]
#[CoversClass(PatchOperation::class)]
#[UsesClass(PatchValidationTrait::class)]
#[UsesClass(CrudTrait::class)]
#[UsesClass(BasicJsonHandler::class)]
#[UsesClass(JsonPointer6901::class)]
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
class CopyTest extends TestCase
{
    private Copy $Operation;

    protected function setUp(): void
    {
        $this->Operation = new Copy();
        $this->Operation->setJsonHandler(new BasicJsonHandler);
    }

    public function testGetOperation(): void
    {
        $this->assertSame('copy', $this->Operation->getOperation());
    }

    public function testValidateHandlesValidPatchStructure(): void
    {
        $this->expectNotToPerformAssertions();
        $patch = (object) [
            'op' => 'copy',
            'path' => '/a/b/c',
            'from' => '/a/d/e'
        ];
        $this->Operation->validate($patch);
    }

    public function testValidateThrowsExceptionOnInvalidFrom(): void
    {
        $this->expectException(InvalidPatchFromException::class);
        /** @var object{op:string, path: string, from: string} $patch */
        $patch = (object) [
            'op' => 'copy',
            'path' => '/a/b/c',
            'from' => null
        ];
        $this->Operation->validate($patch);
    }

    public function testValidateThrowsExceptionOnMalformedFrom(): void
    {
        $this->expectException(MalformedPathException::class);
        $patch = (object) [
            'op' => 'copy',
            'path' => '/a/b/c',
            'from' => 'invalidFrom'
        ];
        $this->Operation->validate($patch);
    }

    public function testApplyCopiesValueToPath(): void
    {
        $document = (object) [
            'a' => (object) [
                'b' => 'c',
                'd' => 'e',
            ]
        ];
        $patch = (object) [
            'op' => 'copy',
            'from' => '/a/b',
            'path' => '/a/d'
        ];
        $expected = (object) [
            'a' => (object) [
                'b' => 'c',
                'd' => 'c',
            ]
        ];
        $this->Operation->apply($document, $patch);
        $this->assertEquals($expected, $document);
    }

    public function testApplyCopiesValueAppendingToArray(): void
    {
        $document = (object) [
            'a' => (object) [
                'b' => 'c',
                'd' => [],
            ]
        ];
        $patch = (object) [
            'op' => 'copy',
            'from' => '/a/b',
            'path' => '/a/d/-'
        ];
        $expected = (object) [
            'a' => (object) [
                'b' => 'c',
                'd' => ['c'],
            ]
        ];
        $this->Operation->apply($document, $patch);
        $this->assertEquals($expected, $document);
    }

    public function testApplyThrowsExceptionWhenCopySourcePathDoesNotExist(): void
    {
        $document = (object) [
            'a' => (object) [
                'd' => 'e',
            ]
        ];
        $patch = (object) [
            'op' => 'copy',
            'from' => '/a/b',
            'path' => '/a/e'
        ];
        $this->expectException(UnknownPathException::class);
        $this->Operation->apply($document, $patch);
    }

    public function testGetRevertPatchReturnsPatchToUndoCopyOperation(): void
    {
        $document = (object) [
            'a' => (object) [
                'b' => 'c',
                'd' => 'e',
            ]
        ];
        $patch = (object) [
            'op' => 'copy',
            'from' => '/a/b',
            'path' => '/a/d'
        ];
        $this->Operation->apply($document, $patch);
        $revertPatch = $this->Operation->getRevertPatch($patch);
        $expected = ['op' => 'replace', 'path' => '/a/d', 'value' => 'e'];
        $this->assertEquals($expected, $revertPatch);
    }

    public function testGetRevertPatchReturnsPatchToRemoveCopiedValueWhenPathWasNonexistent(): void
    {
        $document = (object) [
            'a' => (object) [
                'b' => 'c',
            ]
        ];
        $patch = (object) [
            'op' => 'copy',
            'from' => '/a/b',
            'path' => '/a/d'
        ];
        $this->Operation->apply($document, $patch);
        $revertPatch = $this->Operation->getRevertPatch($patch);
        $expected = ['op' => 'remove', 'path' => '/a/d'];
        $this->assertEquals($expected, $revertPatch);
    }

    public function testGetRevertPatchReturnsPatchToRemoveCopiedValueAppendedToArray(): void
    {
        $document = (object) [
            'a' => (object) [
                'b' => 'c',
                'd' => ['a', 'b'],
            ]
        ];
        $patch = (object) [
            'op' => 'copy',
            'from' => '/a/b',
            'path' => '/a/d/-'
        ];

        $this->Operation->apply($document, $patch);
        $revertPatch = $this->Operation->getRevertPatch($patch);

        $expected = ['op' => 'remove', 'path' => '/a/d/2'];
        $this->assertEquals($expected, $revertPatch);
    }

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testRevertPatchThrowsExceptionWhenJsonHandlerReturnsUnexpectedValueForArrayAppend(): void
    {
        $JsonHandlerMock = $this->createMock(BasicJsonHandler::class);
        $JsonHandlerMock->method('write')->willReturn('unexpected-value');
        $this->Operation->setJsonHandler($JsonHandlerMock);

        $document = (object) [
            'a' => (object) [
                'b' => 'c',
                'd' => [],
            ]
        ];
        $patch = (object) [
            'op' => 'copy',
            'from' => '/a/b',
            'path' => '/a/d/-'
        ];

        $this->Operation->apply($document, $patch);

        $this->expectException(\LogicException::class);
        $this->Operation->getRevertPatch($patch);
    }
}
