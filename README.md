# kensnyder/json-truncator

Encode a value to json but keep it within a designated size.

## Installation

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
  'maxRetries' => 4,
  'decayRate' => 0.75,
]);
```

## Options

- `maxLength`
- `maxItems`
- `maxItemLength`
- `maxRetries`
- `decayRate`

## Unit tests

Unit tests are run through Kahlan.

`sh unit.sh`

Or if you have npm's chokidar-cli installed globally, you can run tests for
every file change:

`sh unit-watch.sh`

## Prettier for development

To use prettier for development, you'll need to install prettier and
@prettier/plugin-php globally via npm and then set up your IDE to support
running prettier on save.

```bash
npm install -g prettier @prettier/plugin-php
```

## License

ISC
