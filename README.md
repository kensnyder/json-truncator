# kensnyder/json-truncator

Installation

`composer install kensnyder/json-truncator`

## Requirements

PHP 7.0+

## Usage

```php
use KenSnyder\JsonTruncator;

$safeJson = JsonTruncator::encode($myValue, [
    'maxLength' => 40000,
    'maxItems' => 100,
    'maxItemLength' => 8000,
    'decay' => 0.5,
]);
```

## Unit tests

Unit tests are run through Kahlan.

`sh unit.sh`

Or if you have npm's chokidar-cli installed globally, you can run tests for
every file change:

`sh unit-watch.sh`

## License

ISC
