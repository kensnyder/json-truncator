<?php

require_once __DIR__ . '/../src/JsonTruncator.php';
require_once __DIR__ . '/../src/InvalidOptionException.php';
require_once __DIR__ . '/fixtures/MyTestClass.php';

use KenSnyder\JsonTruncator;

describe('JsonTruncator::stringify() with 2 rounds, no ellipsis', function () {
	it('should encode short strings', function () {
		$value = 'abc';
		$json = JsonTruncator::stringify($value);
		expect($json)->toBe('"abc"');
	});
	it('should truncate strings', function () {
		$value = 'abcdef';
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 5,
			'maxItemLength' => 5,
			'maxRetries' => 2,
			'ellipsis' => '',
		]);
		expect($json)->toBe('"abc"');
	});
	it('should truncate strings inside arrays', function () {
		$value = ['abcdef', 'ghijkl'];
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 13,
			'maxItemLength' => 5,
			'maxRetries' => 2,
			'ellipsis' => '',
		]);
		expect($json)->toBe('["abc","ghi"]');
	});
	it('should truncate arrays', function () {
		$value = ['abc', 'def', 'ghi'];
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 13,
			'maxItemLength' => 10,
			'maxItems' => 2,
			'ellipsis' => '',
		]);
		expect($json)->toBe('["abc","def"]');
	});
	it('should truncate stdClass objects', function () {
		$value = (object) [
			'one' => 'abc',
			'two' => 'def',
			'three' => 'ghi',
		];
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 25,
			'maxItemLength' => 20,
			'maxItems' => 2,
			'ellipsis' => '',
		]);
		expect($json)->toBe('{"one":"abc","two":"def"}');
	});
	it('should truncate other objects', function () {
		$value = new MyTestClass();
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 25,
			'maxItemLength' => 20,
			'maxItems' => 2,
			'maxRetries' => 2,
			'ellipsis' => '',
		]);
		expect($json)->toBe('{"one":"abc","two":"def"}');
	});
	it('should truncate long array keys', function () {
		$value = [
			'one-hundred-thousand' => 'abc',
			'two' => 'def',
		];
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 25,
			'maxItemLength' => 5,
			'maxRetries' => 2,
			'ellipsis' => '',
		]);
		expect($json)->toBe('{"one":"abc","two":"def"}');
	});
	it('should give up when maxLength is very small - number', function () {
		$value = 123456789;
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 6,
			'maxItemLength' => 5,
			'maxRetries' => 2,
			'ellipsis' => '',
		]);
		expect($json)->toBe('123456');
	});
	it('should give up when maxLength is very small - nested object', function () {
		$value = ['response' => ['data' => ['string']]];
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 25,
			'maxItemLength' => 10,
			'maxRetries' => 1,
			'ellipsis' => '',
		]);
		expect($json)->toBe('{"response":{"data":["str');
	});
});

describe('JsonTruncator::stringify() with 3 rounds, ellipsis', function () {
	it('should truncate strings', function () {
		$value = '1234567890';
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 11,
			'maxItemLength' => 11,
			'maxRetries' => 3,
			'ellipsis' => '...',
		]);
		expect($json)->toBe('"123456..."');
	});
	it('should truncate strings in arrays', function () {
		$value = ['one' => '1234567890'];
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 19,
			'maxItemLength' => 19,
			'maxRetries' => 3,
			'ellipsis' => '...',
		]);
		expect($json)->toBe('{"one":"12345..."}');
	});
	it('should truncate strings in arrays with overage', function () {
		$value = ['one' => '12345678901234567890'];
		$json = JsonTruncator::stringify($value, [
			'maxLength' => 26,
			'maxItemLength' => 26,
			'maxRetries' => 3,
			'ellipsis' => '...[%overage%]',
		]);
		expect($json)->toBe('{"one":"123...[17]"}');
	});
});

describe('JsonTruncator::stringify() option validator', function () {
	it('should throw if decayRate is invalid', function () {
		$attempt = function () {
			$value = [];
			JsonTruncator::stringify($value, ['decayRate' => 1.1]);
		};
		expect($attempt)->toThrow();
	});
	it('should throw if maxRetries is less than 2', function () {
		$attempt = function () {
			$value = [];
			JsonTruncator::stringify($value, ['maxRetries' => -1]);
		};
		expect($attempt)->toThrow();
	});
	it('should throw if maxLength is less than maxItemLength', function () {
		$attempt = function () {
			$value = [];
			JsonTruncator::stringify($value, [
				'maxLength' => 100,
				'maxItemLength' => 101,
			]);
		};
		expect($attempt)->toThrow();
	});
	it('should throw if maxLength is less than 3', function () {
		$attempt = function () {
			$value = [];
			JsonTruncator::stringify($value, [
				'maxLength' => 2,
			]);
		};
		expect($attempt)->toThrow();
	});
	it('should throw if maxItemLength is less than 3', function () {
		$attempt = function () {
			$value = [];
			JsonTruncator::stringify($value, [
				'maxItemLength' => 2,
			]);
		};
		expect($attempt)->toThrow();
	});
	it('should throw if maxItems is less than 1', function () {
		$attempt = function () {
			$value = [];
			JsonTruncator::stringify($value, [
				'maxItems' => 0,
			]);
		};
		expect($attempt)->toThrow();
	});
});
