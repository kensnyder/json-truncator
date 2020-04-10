<?php

require_once __DIR__ . '/../src/JsonTruncator.php';
require_once __DIR__ . '/../src/InvalidOptionException.php';

use KenSnyder\JsonTruncator;

$example = file_get_contents(
	__DIR__ . '/../tests/fixtures/raw-json/log_response.json'
);
$value = json_decode($example);

$report = JsonTruncator::report($value, [
	'maxLength' => 40000,
	'maxItems' => 8,
	'maxItemLength' => 1000,
	//	'ellipsis' => '...snip',
	'maxRetries' => 5,
	'jsonFlags' => JsonTruncator::$defaults['jsonFlags'] + JSON_PRETTY_PRINT,
]);

print_r($report);
echo "\n";
