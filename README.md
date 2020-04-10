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
  'maxItems' => 20,
  'maxItemLength' => 8000,
  'maxRetries' => 5,
  'decayRate' => 0.75,
  'ellipsis' => '...[%overage%]',
  'jsonFlags' => JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES,
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
- `jsonFlags`: The integer total of JSON\_\* constants to use when encoding.
  See php docs for
  [JSON constants](https://www.php.net/manual/en/json.constants.php#constant.json-object-as-array)
- `jsonDepth`: Max depth of nested arrays or objects

## When would it give up?

After retrying truncation strategies `maxRetries` times, truncated (likely
invalid) JSON will be returned.

## Motivation

I use Splunk. To make log values more accessible and searchable, I log values
as JSON. Splunk is able to make JSON searchable by any field at any depth.
Splunk does have a maximum length for each log line. If JSON exceeds that
length, Splunk will cut it off and be unable to parse it as JSON. This library
allows truncating JSON to fit within that line length limit. For more info on
Splunk's limit, check out the docs for configuring
[TRUNCATE](https://docs.splunk.com/Documentation/Splunk/latest/Admin/Propsconf#Line_breaking).

## Unit tests

Unit tests are run through Kahlan.

`sh scripts/unit.sh`

Or if you have npm's chokidar-cli installed globally, you can run tests for
every file change:

`sh scripts/unit-watch.sh`

## Prettier for development

If you choose to prettier for development, you'll need to install prettier and
@prettier/plugin-php globally via npm and then set up your IDE to support
running prettier on save.

```bash
npm install -g prettier @prettier/plugin-php
```

## License

Open source, free for commercial use, [ISC](./LICENSE.md) license.
