PHP Fast JSON Patch
=====================

![Test](https://github.com/blancks/fast-jsonpatch-php/workflows/Test/badge.svg)
![phpstan](https://github.com/blancks/fast-jsonpatch-php/workflows/phpstan/badge.svg)
[![codecov](https://codecov.io/github/blancks/fast-jsonpatch-php/graph/badge.svg?token=3PUC5RAPPQ)](https://codecov.io/github/blancks/fast-jsonpatch-php)
[![PHP Version Require](https://poser.pugx.org/blancks/fast-jsonpatch-php/require/php)](https://packagist.org/packages/blancks/fast-jsonpatch-php)
[![Latest Stable Version](https://poser.pugx.org/blancks/fast-jsonpatch-php/v)](https://packagist.org/packages/blancks/fast-jsonpatch-php)

FastJsonPatch is designed to handle JSON Patch operations in accordance with the [RFC 6902](http://tools.ietf.org/html/rfc6902) specification.

JSON Patch is a format for expressing a sequence of operations to be applied to a JSON document. This class provides methods to parse, validate, and apply these operations, allowing you to modify JSON objects or arrays programmatically.

## Installation via Composer

``` bash
composer require blancks/fast-jsonpatch-php
```

## Benchmark Results

The following table shows the average time each library took to apply a patch with 1000 operations to a target document as summary of the performance.
The benchmark script and full data is available at  [blancks/php-jsonpatch-benchmarks](https://github.com/blancks/php-jsonpatch-benchmarks).

| Library                     | Microseconds |
|-----------------------------|--------------|
| blancks/fast-jsonpatch-php  | 2903         |
| mikemccabe/json-patch-php   | 3355         |
| swaggest/json-diff          | 3638         |
| gamringer/php-json-patch    | 7276         |
| xp-forge/json-patch         | 8534         |
| php-jsonpatch/php-jsonpatch | 10970        |
| remorhaz/php-json-patch     | 870711       |

Performance comparison between releases is still available [here](https://docs.google.com/spreadsheets/d/1YHVZ38GHf0v9nJMCz5Sx_Z5nz8_VKiwnDpaGnnHtiXQ/edit?usp=sharing)

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
    - The class is optimized for performance, time complexity is O(N*P) where N is the number of operations of the patch and where P is the nesting level of patch operations.
    - Best use case is for scenarios where JSON document can be fully loaded into memory, and you need fast patch processing like websockets server/client.


6. **Tests:**
    - Extensive unit testing ensures that everything is robust and works as intended.

## Basic Usage

``` php
<?php require_once 'vendor/autoload.php';

use blancks\JsonPatch\FastJsonPatch;
use blancks\JsonPatch\FastJsonPatch\exceptions\FastJsonPatchException;

$json = '{"name": "John", "age": 30}';
$patch = '[
    {"op": "replace", "path": "/name", "value": "Jane"},
    {"op": "add", "path": "/email", "value": "jane@example.com"}
]';

try {

   echo FastJsonPatch::apply($json, $patch); 
   // Output: {"name": "Jane", "age": 30, "email": "jane@example.com"}
   
} catch(FastJsonPatchException $e) {

   // FastJsonPatchException comes with two additional methods to fetch context data:
   // $e->getContextPointer() may return the context JSON pointer for given error
   // $e->getContextDocument() may return the portion of the document relevant for the error 
   
}
```

## Methods Overview

- `apply(string $json, string $patch): string` Applies the $patch operations to the provided $json document and returns the updated json document string.


- `applyDecoded(string $json, string $patch): mixed` Same as **apply** but returns the decoded document instead of a json string


- `applyByReference(array|\stdClass &$document, array $patch): void` References your in-memory representation of the document and applies the patch in place.


- `parsePath(string $json, string $pointer): mixed` Returns the value located by the given $pointer from the $json string document


- `parsePathByReference(array|\stdClass &$document, string $pointer): mixed` Same as **parsePath** but finds the location from your in-memory document


- `validatePatch(string $patch): void` Checks if the provided $patch is structurally valid

## Running tests

``` bash
composer test
```

Test cases comes from [json-patch/json-patch-tests](https://github.com/json-patch/json-patch-tests) and extended furthermore.

## License

This software is licensed under the [MIT License](LICENSE.md).
