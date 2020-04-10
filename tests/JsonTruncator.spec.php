<?php

require_once(__DIR__ . '/../src/JsonTruncator.php');
require_once(__DIR__ . '/fixtures/MyTestClass.php');

use KenSnyder\JsonTruncator;

describe('JsonTruncator::stringify()', function() {
	it('should encode short strings', function() {
		$value = 'abc';
		$json = JsonTruncator::stringify($value);
		expect($json)->toBe('"abc"');
	});
	it('should truncate strings', function() {
		$value = 'abcdef';
		$json = JsonTruncator::stringify($value, ['maxLength' => 5, 'maxItemLength' => 5]);
		expect($json)->toBe('"abc"');
	});
	it('should truncate strings inside arrays', function() {
		$value = ['abcdef','ghijkl'];
		$json = JsonTruncator::stringify($value, ['maxLength' => 13, 'maxItemLength' => 5]);
		expect($json)->toBe('["abc","ghi"]');
	});
	it('should truncate arrays', function() {
		$value = ['abc','def','ghi'];
		$json = JsonTruncator::stringify($value, ['maxLength' => 13, 'maxItemLength' => 10, 'maxItems' => 2]);
		expect($json)->toBe('["abc","def"]');
	});
	it('should truncate stdClass objects', function() {
		$value = (object) [
			'one' => 'abc',
			'two' => 'def',
			'three' => 'ghi',
		];
		$json = JsonTruncator::stringify($value, ['maxLength' => 25, 'maxItemLength' => 20, 'maxItems' => 2]);
		expect($json)->toBe('{"one":"abc","two":"def"}');
	});
	it('should truncate other objects', function() {
		$value = new MyTestClass();
		$json = JsonTruncator::stringify($value, ['maxLength' => 25, 'maxItemLength' => 20, 'maxItems' => 2]);
		expect($json)->toBe('{"one":"abc","two":"def"}');
	});
	it('should truncate long array keys', function() {
		$value = [
			'one-hundred-thousand' => 'abc',
			'two' => 'def',
		];
		$json = JsonTruncator::stringify($value, ['maxLength' => 25, 'maxItemLength' => 5]);
		expect($json)->toBe('{"one":"abc","two":"def"}');
	});
});

