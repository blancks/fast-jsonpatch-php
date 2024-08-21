PHP Fast JSON Patch
=====================

FastJsonPatch is designed to handle JSON Patch operations in accordance with the [RFC 6902](http://tools.ietf.org/html/rfc6902) specification.

JSON Patch is a format for expressing a sequence of operations to be applied to a JSON document. This class provides methods to parse, validate, and apply these operations, allowing you to modify JSON objects or arrays programmatically.


## Installation via Composer

``` bash
composer require blancks/fast-jsonpatch-php
```

## Key features

1. **Apply JSON Patch Operations:** 
   - The class can apply a series of JSON Patch operations to a target JSON document.
   - The operations are performed sequentially, modifying the document as specified in the patch.


2. **Operation Types:**
   - **add**: Adds a value to a specific location in the JSON document.
   - **copy**: Copies a value from one location to another within the JSON document.
   - **move**: Moves a value from one location to another within the JSON document.
   - **remove**: Removes a value from a specific location in the JSON document.
   - **replace**: Replaces the value at a specific location with a new value.
   - **test**: Tests whether a specified value is present at a specific location in the JSON document.


3. **Path Parsing:**
    - The class uses JSON Pointer ([RFC 6901](https://datatracker.ietf.org/doc/html/rfc6901)) notation to identify locations within the JSON document. It correctly handles the path syntax, including edge cases such as escaping special characters.


4. **Validation:**
    - The class ensures that the provided patch document conforms to the JSON Patch specification, validating the structure and types of operations before applying them.


5. **Performance:**
    - The class is optimized for performance, ensuring that operations are applied efficiently even on large JSON documents.


6. **Tests:**
    - Extensive unit testing ensures that everything is robust and works as intended.

## Basic Usage

``` php
<?php require_once 'vendor/autoload.php';

use blancks\JsonPatch\FastJsonPatch;

$json = '{"name": "John", "age": 30}';
$patch = '[
    {"op": "replace", "path": "/name", "value": "Jane"},
    {"op": "add", "path": "/email", "value": "jane@example.com"}
]';

echo FastJsonPatch::apply($json, $patch); 
// Output: {"name": "Jane", "age": 30, "email": "jane@example.com"}
```

## Methods Overview

- `apply(string $json, string $patch): string` Applies the $patch operations to the provided $json document and returns the updated json document string.


- `applyDecoded(string $json, string $patch): mixed` Same as **apply** but returns instead the decoded document instead of a json string


- `applyByReference(array|\stdClass &$document, array $patch): void` References your in-memory representation of the document and applies the patch in place.


- `parsePath(string $json, string $pointer): mixed` Returns the value located by the given $pointer from the $json string document


- `parsePathByReference(array|\stdClass &$document, string $pointer): mixed` Same as **parsePath** but finds the location from your in-memory document


- `validatePatch(string $patch): void` Checks if the provided $patch is structurally valid

## Dependencies

- PHP >= 8.1
- JSON extension enabled in PHP

## Running tests

``` bash
composer test
```

Test cases comes from [json-patch/json-patch-tests](https://github.com/json-patch/json-patch-tests) and extended furthermore.

## License

This software is licensed under the [MIT License](LICENSE.md).
