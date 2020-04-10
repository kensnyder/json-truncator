<?php

require_once __DIR__ . '/../src/JsonTruncator.php';
require_once __DIR__ . '/../src/InvalidOptionException.php';

use KenSnyder\JsonTruncator;

$example = file_get_contents(
	__DIR__ . '/../tests/fixtures/raw-json/log_response.json'
);
$value = json_decode($example);

$json = JsonTruncator::stringify($value, [
	'maxLength' => 40000,
	'maxItems' => 8,
	'maxItemLength' => 4000,
	'ellipsis' => '...',
	'maxRetries' => 5,
	'jsonFlags' => JsonTruncator::$defaults['jsonFlags'] + JSON_PRETTY_PRINT,
]);

$length = strlen($json);
echo "JSON is now $length long!\n";
echo $json;
echo "\n";
