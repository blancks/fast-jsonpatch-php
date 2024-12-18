<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\operations;

use blancks\JsonPatch\exceptions\FailedTestException;
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
use blancks\JsonPatch\json\pointer\JsonPointer6901;
use blancks\JsonPatch\operations\PatchOperation;
use blancks\JsonPatch\operations\PatchValidationTrait;
use blancks\JsonPatch\operations\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Test::class)]
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
#[UsesClass(FailedTestException::class)]
#[UsesClass(InvalidPatchFromException::class)]
#[UsesClass(MalformedPathException::class)]
#[UsesClass(UnknownPathException::class)]
#[UsesClass(InvalidPatchValueException::class)]
#[UsesClass(FailedTestException::class)]
class TestTest extends TestCase
{
    private Test $Operation;

    protected function setUp(): void
    {
        $JsonHandler = new BasicJsonHandler;
        $JsonPointerHandler = new JsonPointer6901;
        $JsonHandler->setJsonPointerHandler($JsonPointerHandler);

        $this->Operation = new Test();
        $this->Operation->setJsonHandler($JsonHandler);
        $this->Operation->setJsonPointerHandler($JsonPointerHandler);
    }

    public function testGetOperation(): void
    {
        $this->assertSame('test', $this->Operation->getOperation());
    }

    public function testValidateSuccess(): void
    {
        $this->expectNotToPerformAssertions();
        $patch = (object) ['op' => 'test', 'path' => '/path/to/test', 'value' => 'test_value'];
        $this->Operation->validate($patch);
    }

    public function testValidateFailure(): void
    {
        $this->expectException(InvalidPatchValueException::class);
        /** @var object{op:string, path: string, value: mixed} $patch */
        $patch = (object) [
            'op' => 'test',
            'path' => '/path/to/test',  /* no value */
        ];
        $this->Operation->validate($patch);
    }

    public function testApplySuccessOnAssociativeDocument(): void
    {
        $this->expectNotToPerformAssertions();
        $document = ['path' => ['to' => ['test' => 'test_value']]];
        $patch = (object) ['op' => 'test', 'path' => '/path/to/test', 'value' => 'test_value'];
        $this->Operation->apply($document, $patch);
    }

    public function testApplySuccessOnObjectDocument(): void
    {
        $this->expectNotToPerformAssertions();
        $document = (object) ['path' => (object) ['to' => (object) ['test' => 'test_value']]];
        $patch = (object) ['op' => 'test', 'path' => '/path/to/test', 'value' => 'test_value'];
        $this->Operation->apply($document, $patch);
    }

    public function testApplySuccessWithNullValue(): void
    {
        $this->expectNotToPerformAssertions();
        $document = ['path' => ['to' => ['test' => null]]];
        $patch = (object) ['op' => 'test', 'path' => '/path/to/test', 'value' => null];
        $this->Operation->apply($document, $patch);
    }

    public function testApplySuccessWithFalseValue(): void
    {
        $this->expectNotToPerformAssertions();
        $document = ['path' => ['to' => ['test' => false]]];
        $patch = (object) ['op' => 'test', 'path' => '/path/to/test', 'value' => false];
        $this->Operation->apply($document, $patch);
    }

    public function testApplySuccessWithArrayValue(): void
    {
        $this->expectNotToPerformAssertions();
        $document = ['path' => ['to' => ['test' => 'value']]];
        $patch = (object) ['op' => 'test', 'path' => '/path', 'value' => ['to' => ['test' => 'value']]];
        $this->Operation->apply($document, $patch);
    }

    public function testApplySuccessWithObjectValue(): void
    {
        $this->expectNotToPerformAssertions();
        $document = (object) ['path' => (object) ['to' => (object) ['test' => 'value']]];
        $patch = (object) ['op' => 'test', 'path' => '/path', 'value' => (object) ['to' => (object) ['test' => 'value']]];
        $this->Operation->apply($document, $patch);
    }

    public function testApplyFailureDueToInvalidPath(): void
    {
        $this->expectException(UnknownPathException::class);
        $document = 'wrong_test_value';
        $patch = (object) ['op' => 'test', 'path' => '/path/to/test', 'value' => 'test_value'];
        $this->Operation->apply($document, $patch);
    }

    public function testApplyFailureDueToUnexpectedValue(): void
    {
        $this->expectException(FailedTestException::class);
        $document = ['path' => ['to' => ['test' => null]]];
        $patch = (object) ['op' => 'test', 'path' => '/path/to/test', 'value' => 'test_value'];
        $this->Operation->apply($document, $patch);
    }

    public function testGetRevertPatch(): void
    {
        $patch = (object) ['op' => 'test', 'path' => '/path/to/test', 'value' => 'test_value'];
        $this->assertNull($this->Operation->getRevertPatch($patch));
    }
}
