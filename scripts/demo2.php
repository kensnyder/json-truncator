<?php

require_once __DIR__ . '/../src/JsonTruncator.php';
require_once __DIR__ . '/../src/InvalidOptionException.php';

use KenSnyder\JsonTruncator;

$example = file_get_contents(
	__DIR__ . '/../tests/fixtures/raw-json/array_of_strings.json'
);
$value = json_decode($example);

$report = JsonTruncator::report($value, [
	'maxLength' => 10000,
	'maxItems' => 200,
	'maxItemLength' => 4000,
	//	'ellipsis' => '...',
	'maxRetries' => 10,
	'jsonFlags' => JsonTruncator::$defaults['jsonFlags'] + JSON_PRETTY_PRINT,
]);

print_r($report);
echo "\n";
