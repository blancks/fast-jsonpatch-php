PHP Fast JSON Patch
=====================

![Test](https://github.com/blancks/fast-jsonpatch-php/workflows/Test/badge.svg)
![phpstan](https://github.com/blancks/fast-jsonpatch-php/workflows/phpstan/badge.svg)
[![codecov](https://codecov.io/gh/blancks/fast-jsonpatch-php/branch/dev-v2/graph/badge.svg?token=3PUC5RAPPQ)](https://codecov.io/gh/blancks/fast-jsonpatch-php)
[![maintainability](https://api.codeclimate.com/v1/badges/44af70d9b23b5f6c7760/maintainability)](https://codeclimate.com/github/blancks/fast-jsonpatch-php)
[![PHP Version Require](https://poser.pugx.org/blancks/fast-jsonpatch-php/require/php)](https://packagist.org/packages/blancks/fast-jsonpatch-php)
[![Latest Stable Version](https://poser.pugx.org/blancks/fast-jsonpatch-php/v)](https://packagist.org/packages/blancks/fast-jsonpatch-php)

This documentation covers the `FastJsonPatch` PHP class, designed to apply a series of JSON Patch operations as specified in [RFC 6902](https://datatracker.ietf.org/doc/html/rfc6902). JSON Patch is a format for describing changes to a JSON document.

## Installation via Composer

``` bash
composer require blancks/fast-jsonpatch-php
```

---

## Class Overview

The `FastJsonPatch` class provides a way to modify JSON documents using a structured patch object. The patch object contains an array of operations (`add`, `remove`, `replace`, `move`, `copy`, and `test`) that describe the changes to be made to the target JSON document.

---

### Usage Example

Below is an example of how to use the `FastJsonPatch` class to apply a patch to a JSON document:

```php
use blancks\JsonPatch\FastJsonPatch;

$document = '{"foo":"bar","baz":["qux","quux"]}';

$patch = '[
    {"op":"replace","path":"\/baz\/1","value":"boo"},
    {"op":"add","path":"\/hello","value":{"world":"wide"}},
    {"op":"remove","path":"\/foo"}
]';

$FastJsonPatch = FastJsonPatch::fromJson($document);
$FastJsonPatch->apply($patch);

print_r($FastJsonPatch->getDocument());
```

**Expected Output:**

```php
[
    "baz" => ["qux", "boo"],
    "hello" => ["world" => "wide"]
]
```

If the document is already json-decoded in your code you can just pass it to the class constructor instead:

```php
use blancks\JsonPatch\FastJsonPatch;

$document = [
    "foo" => "bar",
    "baz" => ["qux", "quux"]
];

$patch = '[
    {"op":"replace","path":"\/baz\/1","value":"boo"},
    {"op":"add","path":"\/hello","value":{"world":"wide"}},
    {"op":"remove","path":"\/foo"}
]';

$FastJsonPatch = new FastJsonPatch($document);
$FastJsonPatch->apply($patch);

// $document is edited by reference
print_r($document);
```

---

## Constructor

### `__construct(mixed &$document, ?JsonHandlerInterface $JsonHandler = null)`

- **Description**: Initializes a new instance of the `FastJsonPatch` class.
- **Parameters**:
  - `mixed &$document`: The decoded JSON document.
  - `?JsonHandlerInterface $JsonHandler`: An instance of the JSON handler which will be responsible for encoding/decoding and CRUD operations.\
    The default handler is the `BasicJsonHandler` class and decodes json objects as php \stdClass instances. This is the recommended way.\
    If you cannot avoid working with associative arrays, you can pass a `ArrayJsonHandler` instance instead.
- **Returns**: Instance of the `FastJsonPatch` class.

---

## Public Methods

### `static function fromJson(string $patch, ?JsonHandlerInterface $JsonHandler = null) : void`

- **Description**: Returns a new instance of the `FastJsonPatch` class.
- **Parameters**:
  - `string $document`: A json encoded document to which the patches will be applied
  - `?JsonHandlerInterface $JsonHandler`: An instance of the JSON handler which will be responsible for encoding/decoding and CRUD operations.\
    The default handler is the `BasicJsonHandler` class and decodes json objects as php \stdClass instances. This is the recommended way.\
    If you cannot avoid working with associative arrays, you can pass a `ArrayJsonHandler` instance instead.
- **Example**:
  ```php
  $FastJsonPatch = FastJsonPatch::fromJson('{"foo":"bar","baz":["qux","quux"]}');
  ```

---

### `function apply(string $patch) : void`

- **Description**: Applies a series of patch operations to the specified JSON document. Ensures atomicity by applying all operations successfully or making no changes at all if any operation fails.
- **Parameters**:
  - `string $patch`: A json-encoded array of patch operations.
- **Exceptions**:
  - Throws `FastJsonPatchValidationException` if a patch operation is invalid or improperly formatted.
  - Throws `FastJsonPatchException` if any other error occurs while applying the patch
- **Example**:
  ```php
  $FastJsonPatch->apply($patch);
  ```

---

### `function isValidPatch(string $patch): bool`

- **Description**: Tells if the $patch passes the validation
- **Parameters**:
  - `string $patch`: A json-encoded array of patch operations.
- **Returns**: True is the patch is valid, false otherwise
- **Example**:
  ```php
  $patch = '[{"op":"add","path":"/foo"}]'; // invalid because there's no "value" key 
 
  if ($FastJsonPatch->isValidPatch($patch)) {
    $FastJsonPatch->apply($patch);
  } else {
    echo "Invalid patch!";
  }
  ```

---

### `function read(string $path): mixed`

- **Description**: Uses a JSON Pointer (RFC-6901) to fetch data from the referenced document
- **Parameters**:
  - `string $patch`: A json pointer
- **Returns**: The value located by the provided pointer
- **Example**:
  ```php
  $FastJsonPatch = FastJsonPatch::fromJson('{"foo":"bar","baz":["qux","quux"]}')
  echo $FastJsonPatch->read('/baz/1'); // "quux"
  ```

---

### `function &getDocument(): mixed`

- **Description**: Returns the document reference that the instance is holding
- **Returns**: The referenced document
- **Example**:
  ```php
  $FastJsonPatch = FastJsonPatch::fromJson('["qux","quux"]')
  var_dump($FastJsonPatch->getDocument()); // array(2) {[0]=> string(3) "qux" [1]=> string(4) "quux"}
  ```

---

### `function registerOperation(PatchOperationInterface $PatchOperation): void`

- **Description**: Allows to register new patch operation handlers or to override existing ones.
- **Parameters**:
  - `PatchOperationInterface $PatchOperation`: The handler class for handling the operation.
- **Example**:
  ```php
  $FastJsonPatch->registerOperation(new Add);
  ```

---

## Supported Operations

#### `add`

- **Description**: Adds a value to the specified path in the document. Creates any necessary intermediate nodes.
- **Parameters**:
    - `path`: JSON Pointer to the location where the value should be added.
    - `value`: The value to add.
- **Example**:
  ```json
  {"op":"add","path":"/new/key","value":"example"}
  ```

#### `remove`

- **Description**: Removes the value at the specified path.
- **Parameters**:
    - `path`: JSON Pointer to the location of the value to remove.
- **Example**:
  ```php
  {"op":"remove","path":"/old/key"}
  ```

#### `replace`

- **Description**: Replaces the value at the specified path with a new value.
- **Parameters**:
    - `path`: JSON Pointer to the location of the value to replace.
    - `value`: The new value.
- **Example**:
  ```json
  {"op":"replace","path":"/replace/key","value":"newValue"}
  ```

#### `move`

- **Description**: Moves the value from one path to another.
- **Parameters**:
    - `from`: JSON Pointer to the source location of the value to move.
    - `path`: JSON Pointer to the destination location.
- **Example**:
  ```json
  {"op":"move","from":"/old/key","path":"/new/key"}
  ```

#### `copy`

- **Description**: Copies the value from one path to another.
- **Parameters**:
    - `from`: JSON Pointer to the source location of the value to copy.
    - `path`: JSON Pointer to the destination location.
- **Example**:
  ```json
  {"op":"copy","from":"/source/key","path":"/target/key"}
  ```

#### `test`

- **Description**: Tests that the specified value matches the value at the path.
- **Parameters**:
    - `path`: JSON Pointer to the location of the value to test.
    - `value`: The value to compare.
- **Example**:
  ```json
  {"op":"test","path":"/test/key","value":"expectedValue"}
  ```

---

## Running tests

``` bash
composer test
```

Test cases comes from [json-patch/json-patch-tests](https://github.com/json-patch/json-patch-tests) and extended furthermore.

## License

This software is licensed under the [MIT License](LICENSE.md).