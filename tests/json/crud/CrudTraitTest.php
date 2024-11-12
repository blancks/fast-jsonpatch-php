<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\json\crud;

use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\json\accessors\ArrayAccessorInterface;
use blancks\JsonPatch\json\accessors\ObjectAccessorInterface;
use blancks\JsonPatch\json\accessors\ValueAccessorInterface;
use blancks\JsonPatch\json\crud\CrudInterface;
use blancks\JsonPatch\json\crud\CrudTrait;
use blancks\JsonPatch\json\pointer\JsonPointer6901;
use blancks\JsonPatch\json\pointer\JsonPointerHandlerAwareInterface;
use blancks\JsonPatch\json\pointer\JsonPointerHandlerAwareTrait;
use blancks\JsonPatch\json\pointer\JsonPointerHandlerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(CrudTrait::class)]
#[UsesClass(UnknownPathException::class)]
class CrudTraitTest extends TestCase
{
    private CrudInterface $crud;

    /**
     * @var ArrayAccessorInterface&MockObject
     */
    private ArrayAccessorInterface $ArrayAccessorMock;

    /**
     * @var ObjectAccessorInterface&MockObject
     */
    private ObjectAccessorInterface $ObjectAccessorMock;

    /**
     * @var ValueAccessorInterface&MockObject
     */
    private ValueAccessorInterface $ValueAccessorMock;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->crud = new class implements CrudInterface, JsonPointerHandlerInterface, JsonPointerHandlerAwareInterface {
            use CrudTrait;
            use JsonPointerHandlerAwareTrait;

            public ArrayAccessorInterface $ArrayAccessor;
            public ObjectAccessorInterface $ObjectAccessor;
            public ValueAccessorInterface $ValueAccessor;

            public function __construct()
            {
                $this->setJsonPointerHandler(new JsonPointer6901);
            }

            public function isValidPointer(string $pointer): bool
            {
                return $this->jsonPointerHandler->isValidPointer($pointer);
            }

            public function getTokensFromPointer(string $pointer): array
            {
                return $this->jsonPointerHandler->getTokensFromPointer($pointer);
            }
        };
        $this->ArrayAccessorMock = $this->createMock(ArrayAccessorInterface::class);
        $this->ObjectAccessorMock = $this->createMock(ObjectAccessorInterface::class);
        $this->ValueAccessorMock = $this->createMock(ValueAccessorInterface::class);
        $this->crud->ArrayAccessor = $this->ArrayAccessorMock;
        $this->crud->ObjectAccessor = $this->ObjectAccessorMock;
        $this->crud->ValueAccessor = $this->ValueAccessorMock;
    }

    public function testWrite(): void
    {
        $document = ['key' => 'value'];
        $path = '/key';
        $value = 'new_value';

        $this->ValueAccessorMock->method('write')->willReturn(['key' => $value]);
        $result = $this->crud->write($document, $path, $value);
        $this->assertSame(['key' => $value], $result);
    }

    public function testWriteThrowsUnknownPathException(): void
    {
        $document = 'not_an_array_or_object';
        $path = '/key';
        $value = 'new_value';

        $this->expectException(UnknownPathException::class);
        $this->crud->write($document, $path, $value);
    }

    public function testRead(): void
    {
        $document = [['key' => 'value']];
        $path = '/0/key';

        $this->ArrayAccessorMock->method('get')->willReturn(['key' => 'value']);
        $this->ValueAccessorMock->method('read')->willReturn('value');
        $result = $this->crud->read($document, $path);
        $this->assertSame('value', $result);
    }

    public function testReadWithObjects(): void
    {
        $document = (object) [(object) ['key' => 'value']];
        $path = '/0/key';

        $this->ObjectAccessorMock->method('get')->willReturn((object) ['key' => 'value']);
        $this->ValueAccessorMock->method('read')->willReturn('value');
        $result = $this->crud->read($document, $path);
        $this->assertSame('value', $result);
    }

    public function testReadScalarValues(): void
    {
        $document = 'value';
        $path = '';

        $this->ValueAccessorMock->method('read')->willReturn('value');
        $result = $this->crud->read($document, $path);
        $this->assertSame('value', $result);
    }

    public function testReadThrowsUnknownPathException(): void
    {
        $document = 'not_an_array_or_object';
        $path = '/key';

        $this->expectException(UnknownPathException::class);
        $this->crud->read($document, $path);
    }

    public function testUpdate(): void
    {
        $document = ['key' => 'old_value'];
        $path = '/key';
        $new_value = 'new_value';

        $this
            ->ValueAccessorMock
            ->expects($this->once())
            ->method('delete')
            ->willReturn('old_value');

        $this
            ->ValueAccessorMock
            ->expects($this->once())
            ->method('write')
            ->willReturn(['key' => $new_value]);

        $previous = $this->crud->update($document, $path, $new_value);
        $this->assertSame('old_value', $previous);
    }

    public function testUpdateThrowsUnknownPathException(): void
    {
        $document = 'not_an_array_or_object';
        $path = '/key';
        $new_value = 'new_value';

        $this->expectException(UnknownPathException::class);
        $this->crud->update($document, $path, $new_value);
    }

    public function testDelete(): void
    {
        $document = ['key' => 'value'];
        $path = '/key';

        $this->ValueAccessorMock->method('delete')->willReturn('value');
        $result = $this->crud->delete($document, $path);
        $this->assertSame('value', $result);
    }

    public function testDeleteThrowsUnknownPathException(): void
    {
        $document = 'not_an_array_or_object';
        $path = '/key';

        $this->expectException(UnknownPathException::class);
        $this->crud->delete($document, $path);
    }
}
