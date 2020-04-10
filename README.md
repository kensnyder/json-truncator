# kensnyder/json-truncator

Encode a value to json but keep it within a designated string length.

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
  'maxRetries' => 5,
  'decayRate' => 0.75,
  'ellipsis' => '...[%overage%]',
  'jsonFlags' => [JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES],
  'jsonDepth' => 512,
]);
```

## Options

- `maxLength`: Total byte length that the JSON string may occupy
- `maxItems`: Max number of items in an array/object
- `maxItemLength`: Max string length of array/object members
- `maxRetries`: Max number of json_encode attempts
- `decayRate`: How much to reduce limits on subsequent attempts
- `ellipsis`: The characters to append to truncated strings
- `jsonFlags`: The JSON\_\* constants to use when encoding. See php docs on
  [JSON constants](https://www.php.net/manual/en/json.constants.php#constant.json-object-as-array)
- `jsonDepth` Max depth of nested arrays or objects

## When would it give up?

After retrying truncation strategies `maxRetries` times, truncated (likely
invalid) JSON will be returned.

## Unit tests

Unit tests are run through Kahlan.

`sh unit.sh`

Or if you have npm's chokidar-cli installed globally, you can run tests for
every file change:

`sh unit-watch.sh`

## Prettier for development

If you choose to prettier for development, you'll need to install prettier and
@prettier/plugin-php globally via npm and then set up your IDE to support
running prettier on save.

```bash
npm install -g prettier @prettier/plugin-php
```

## License

Open source, free for commercial use, [ISC](./LICENSE.md) license.
