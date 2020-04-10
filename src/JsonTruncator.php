<?php

namespace KenSnyder;

require_once __DIR__ . '/InvalidOptionException.php';

/**
 * Encode a value to json but keep it within a designated size
 * @package KenSnyder
 */
class JsonTruncator {
	/**
	 * Default options for json encoding
	 * @var array
	 * @property int maxLength  Total length that the JSON may occupy
	 * @property int maxItems  Max number of items in an array/object
	 * @property int maxItemLength  Max string length of array/object members
	 * @property float decayRate  How much to reduce limits on subsequent attempts
	 * @property int maxAttempts  Max number of json_encode attempts
	 * @property string ellipsis  The characters to append to truncated strings
	 */
	public static $defaults = [
		'maxLength' => 40000,
		'maxItems' => 100,
		'maxItemLength' => 8000,
		'decayRate' => 0.75,
		'maxAttempts' => 4,
		'ellipsis' => '...[%overage%]',
		'jsonFlags' => [JSON_UNESCAPED_UNICODE, JSON_UNESCAPED_SLASHES],
		'jsonDepth' => 512,
	];

	/**
	 * Run json_encode but ensure that result string is shorter than the maxLength
	 * @param mixed $value  The value to encode
	 * @param array $options  Maximum values and decay rate (see $defaults)
	 * @return string
	 * @throws InvalidOptionException if $options contains invalid values
	 */
	public static function stringify($value, array $options = []): string {
		$options = array_merge(static::$defaults, $options);
		$options['jsonBitmask'] = array_sum($options['jsonFlags']);
		static::_validateOptions($options);
		return static::_attempt($value, $options);
	}

	/**
	 * Ensure options are valid
	 * @param array $options  The options as outlined in $defaults
	 * @throws InvalidOptionException
	 */
	public static function _validateOptions(array $options) {
		if (
			!is_float($options['decayRate']) ||
			$options['decayRate'] <= 0 ||
			$options['decayRate'] >= 1
		) {
			throw new InvalidOptionException(
				'decayRate must be a number between 0 and 1'
			);
		}
		if ($options['maxLength'] < $options['maxItemLength']) {
			throw new InvalidOptionException(
				'maxItemLength must less or equal to than maxLength'
			);
		}
		if ($options['maxLength'] < 3) {
			throw new InvalidOptionException('maxLength must be at least 3');
		}
		if ($options['maxItemLength'] < 3) {
			throw new InvalidOptionException('maxItemLength must be at least 3');
		}
		if ($options['maxItems'] < 1) {
			throw new InvalidOptionException('maxItems must be at least 1');
		}
		if ($options['maxAttempts'] < 1) {
			throw new InvalidOptionException('maxAttempts must be at least 1');
		}
	}

	/**
	 * Recursively attempt to encode, truncqting each time
	 * @param mixed $value  The value to encode
	 * @param array $options  The options as outlined in $defaults
	 * @return string
	 */
	public static function _attempt($value, array $options): string {
		$json = json_encode($value, $options['jsonBitmask'], $options['jsonDepth']);
		if (strlen($json) <= $options['maxLength']) {
			return $json;
		}
		$value = static::_walk($value, $options);
		$newOptions = static::_decay($options);
		if ($newOptions['maxAttempts'] <= 0) {
			$json = json_encode($value, $options['jsonBitmask'], $options['jsonDepth']);
			return substr($json, 0, $options['maxLength']);
		}
		return static::_attempt($value, $newOptions);
	}

	public static function _decay(array $options): array {
		return [
			'maxLength' => $options['maxLength'],
			'maxItems' => max(floor($options['maxItems'] * $options['decayRate']), 1),
			'maxItemLength' => max(
				floor($options['maxItemLength'] * $options['decayRate']),
				3
			),
			'maxAttempts' => $options['maxAttempts'] - 1,
			'decayRate' => $options['decayRate'],
			'jsonFlags' => $options['jsonFlags'],
			'jsonBitmask' => $options['jsonBitmask'],
			'jsonDepth' => $options['jsonDepth'],
		];
	}

	public static function _walk(&$value, array $options = []) {
		if (is_string($value)) {
			$max = $options['maxItemLength'] - mb_strlen($options['ellipsis']) - 2;
			if ($options['ellipsis']) {
				$overage = mb_strlen($value) - $max;
				$short = mb_substr($value, 0, $max);
				$ellipsis = str_replace('%overage%', $overage, $options['ellipsis']);
				return $short . $ellipsis;
			}
			return mb_substr($value, 0, $max);
		}
		if (is_object($value)) {
			$value = get_object_vars($value);
		}
		if (!is_array($value)) {
			return $value;
		}
		$i = 0;
		$keysToTruncate = [];
		$keysToRemove = [];
		foreach ($value as $key => &$item) {
			if ($i++ >= $options['maxItems']) {
				$keysToRemove[] = $key;
				continue;
			}
			if (is_string($key) && mb_strlen($key) > $options['maxItemLength'] - 2) {
				$keysToTruncate[] = $key;
			}
			$item = static::_walk($item, $options);
		}
		if (!empty($keysToTruncate)) {
			$newArray = [];
			foreach ($value as $key => &$item) {
				if (in_array($key, $keysToTruncate)) {
					$key = mb_substr($key, 0, $options['maxItemLength'] - 2);
				}
				$newArray[$key] = $item;
			}
			$value = $newArray;
		}
		if (!empty($keysToRemove)) {
			foreach ($keysToRemove as $key) {
				unset($value[$key]);
			}
		}
		return $value;
	}
}
