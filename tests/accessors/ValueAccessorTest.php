<?php declare(strict_types=1);

namespace blancks\JsonPatchTest\accessors;

use blancks\JsonPatch\exceptions\ArrayBoundaryException;
use blancks\JsonPatch\exceptions\UnknownPathException;
use blancks\JsonPatch\json\accessors\ArrayAccessor;
use blancks\JsonPatch\json\accessors\ObjectAccessor;
use blancks\JsonPatch\json\accessors\ValueAccessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValueAccessor::class)]
#[UsesClass(ArrayAccessor::class)]
#[UsesClass(ObjectAccessor::class)]
#[UsesClass(ArrayBoundaryException::class)]
#[UsesClass(UnknownPathException::class)]
class ValueAccessorTest extends TestCase
{
    private ValueAccessor $ValueAccessor;

    protected function setUp(): void
    {
        $this->ValueAccessor = new ValueAccessor();
    }

    public function testWriteWithArrayAccessorAndNullToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['foo' => 'bar'];
        $path = '/foo';
        $token = null;
        $value = 'baz';

        $previous = $this->ValueAccessor->write($ArrayAccessor, $document, $path, $token, $value);
        $this->assertSame(['foo' => 'bar'], $previous);
    }

    public function testWriteWithArrayAccessorAndNonNullToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['foo' => 'bar'];
        $path = '/foo';
        $token = 'foo';
        $value = 'baz';

        $previous = $this->ValueAccessor->write($ArrayAccessor, $document, $path, $token, $value);
        $this->assertSame('bar', $previous);
    }

    public function testWriteWithArrayAccessorAndAppendToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['bar'];
        $path = '';
        $token = '-';
        $value = 'baz';

        $previous = $this->ValueAccessor->write($ArrayAccessor, $document, $path, $token, $value);
        $this->assertSame(1, $previous);
        $this->assertSame(['bar', 'baz'], $document);
    }

    public function testWriteWithArrayAccessorAndOutOfBoundToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['bar'];
        $path = '';
        $token = '/2';
        $value = 'baz';

        $this->expectException(ArrayBoundaryException::class);
        $this->ValueAccessor->write($ArrayAccessor, $document, $path, $token, $value);
    }

    public function testWriteWithObjectAccessorAndNullToken(): void
    {
        $ObjectAccessor = new ObjectAccessor();
        $document = (object) ['foo' => 'bar'];
        $path = '/foo';
        $token = null;
        $value = 'baz';

        $previous = $this->ValueAccessor->write($ObjectAccessor, $document, $path, $token, $value);
        $this->assertEquals((object) ['foo' => 'bar'], $previous);
    }

    public function testWriteWithObjectAccessorAndNonNullToken(): void
    {
        $ObjectAccessor = new ObjectAccessor();
        $document = (object) ['foo' => 'bar'];
        $path = '/foo';
        $token = 'foo';
        $value = 'baz';

        $result = $this->ValueAccessor->write($ObjectAccessor, $document, $path, $token, $value);
        $this->assertSame('bar', $result);
    }

    public function testReadWithArrayAccessorAndNullToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['foo' => 'bar'];
        $path = '/foo';
        $token = null;

        $result = &$this->ValueAccessor->read($ArrayAccessor, $document, $path, $token);
        $this->assertSame(['foo' => 'bar'], $result);
    }

    public function testReadWithArrayAccessorAndNonNullToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['foo' => 'bar'];
        $path = '/foo';
        $token = 'foo';

        $result = &$this->ValueAccessor->read($ArrayAccessor, $document, $path, $token);
        $this->assertSame('bar', $result);
    }

    public function testReadWithArrayAccessorAndUndefinedToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['foo' => 'bar'];
        $path = '/foo';
        $token = 'biz';

        $this->expectException(UnknownPathException::class);
        $this->ValueAccessor->read($ArrayAccessor, $document, $path, $token);
    }

    public function testReadWithObjectAccessorAndNullToken(): void
    {
        $ObjectAccessor = new ObjectAccessor();
        $document = (object) ['foo' => 'bar'];
        $path = '/foo';
        $token = null;

        $result = &$this->ValueAccessor->read($ObjectAccessor, $document, $path, $token);
        $this->assertEquals((object) ['foo' => 'bar'], $result);
    }

    public function testReadWithObjectAccessorAndNonNullToken(): void
    {
        $ObjectAccessor = new ObjectAccessor();
        $document = (object) ['foo' => 'bar'];
        $path = '/foo';
        $token = 'foo';

        $result = &$this->ValueAccessor->read($ObjectAccessor, $document, $path, $token);
        $this->assertSame('bar', $result);
    }

    public function testReadWithObjectAccessorAndUndefinedToken(): void
    {
        $ObjectAccessor = new ObjectAccessor();
        $document = (object) ['foo' => 'bar'];
        $path = '/foo';
        $token = 'biz';

        $this->expectException(UnknownPathException::class);
        $this->ValueAccessor->read($ObjectAccessor, $document, $path, $token);
    }

    public function testDeleteWithArrayAccessorAndNullToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['foo' => 'bar'];
        $path = '/foo';
        $token = null;

        $previous = $this->ValueAccessor->delete($ArrayAccessor, $document, $path, $token);
        $this->assertSame(['foo' => 'bar'], $previous);
    }

    public function testDeleteWithArrayAccessorAndNonNullToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['foo' => 'bar'];
        $path = '/foo';
        $token = 'foo';

        $previous = $this->ValueAccessor->delete($ArrayAccessor, $document, $path, $token);
        $this->assertSame('bar', $previous);
    }

    public function testDeleteWithArrayAccessorAndUndefinedToken(): void
    {
        $ArrayAccessor = new ArrayAccessor();
        $document = ['foo' => 'bar'];
        $path = '/foo';
        $token = 'biz';

        $this->expectException(UnknownPathException::class);
        $this->ValueAccessor->delete($ArrayAccessor, $document, $path, $token);
    }

    public function testDeleteWithObjectAccessorAndNullToken(): void
    {
        $ObjectAccessor = new ObjectAccessor();
        $document = (object) ['foo' => 'bar'];
        $path = '/foo';
        $token = null;

        $previous = $this->ValueAccessor->delete($ObjectAccessor, $document, $path, $token);
        $this->assertEquals((object) ['foo' => 'bar'], $previous);
    }

    public function testDeleteWithObjectAccessorAndNonNullToken(): void
    {
        $ObjectAccessor = new ObjectAccessor();
        $document = (object) ['foo' => 'bar'];
        $path = '/foo';
        $token = 'foo';

        $previous = $this->ValueAccessor->delete($ObjectAccessor, $document, $path, $token);
        $this->assertSame('bar', $previous);
    }

    public function testDeleteWithObjectAccessorAndUndefinedToken(): void
    {
        $ObjectAccessor = new ObjectAccessor();
        $document = (object) ['foo' => 'bar'];
        $path = '/foo';
        $token = 'biz';

        $this->expectException(UnknownPathException::class);
        $this->ValueAccessor->delete($ObjectAccessor, $document, $path, $token);
    }
}
