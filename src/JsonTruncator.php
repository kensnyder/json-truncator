<?php

namespace KenSnyder;

require_once __DIR__ . '/InvalidOptionException.php';

/**
 * Encode a value to json but keep it within a designated string length.
 * @package KenSnyder
 */
class JsonTruncator {
	/**
	 * Default options for json encoding
	 * @var array
	 * @property int maxLength  Total byte length that the JSON string may occupy
	 * @property int maxItems  Max number of items in an array/object
	 * @property int maxItemLength  Max string length of array/object members
	 * @property int maxRetries  Max number of times to retry json_encode
	 * @property float decayRate  How much to reduce limits on subsequent attempts
	 * @property string ellipsis  The characters to append to truncated strings
	 * @property array jsonFlags  The integer total of JSON_* constants to use when encoding
	 * @see https://www.php.net/manual/en/json.constants.php#constant.json-object-as-array
	 * @property array jsonDepth  Max depth of nested arrays or objects
	 */
	public static $defaults = [
		'maxLength' => 40000,
		'maxItems' => 8,
		'maxItemLength' => 4000,
		'maxRetries' => 8,
		'decayRate' => 0.75,
		'ellipsis' => '...[%overage%]',
		'jsonFlags' => JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES,
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
		static::_validateOptions($options);
		return static::_attempt($value, $options);
	}

	/**
	 * Ensure options are valid
	 * @param array $options  The options as outlined in $defaults
	 * @throws InvalidOptionException
	 */
	protected static function _validateOptions(array $options) {
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
		if ($options['maxRetries'] < 1) {
			throw new InvalidOptionException('maxRetries must be at least 1');
		}
		if (!is_int($options['jsonFlags'])) {
			throw new InvalidOptionException('jsonFlags must be an array of integers');
		}
	}

	/**
	 * Recursively attempt to encode, truncqting each time
	 * @param mixed $value  The value to encode
	 * @param array $options  The options as outlined in $defaults
	 * @return string
	 */
	protected static function _attempt($value, array $options): string {
		$json = json_encode($value, $options['jsonFlags'], $options['jsonDepth']);
		// use strlen and not mb_strlen because we care about byte length
		if (strlen($json) <= $options['maxLength']) {
			return $json;
		}
		$value = static::_walk($value, $options);
		$newOptions = static::_decay($options);
		if ($newOptions['maxRetries'] <= 0) {
			// give up!
			$json = json_encode($value, $options['jsonFlags'], $options['jsonDepth']);
			return substr($json, 0, $options['maxLength']);
		}
		return static::_attempt($value, $newOptions);
	}

	/**
	 * Return a new set of options with reduced values for maxItems and
	 * maxItemLength based on decayRate
	 * @param array $options  Options as defined in static::$defaults
	 * @return array  New options
	 */
	protected static function _decay(array $options): array {
		$newOpts = $options;
		$newOpts['maxItems'] = max(
			floor($options['maxItems'] * $options['decayRate']),
			1
		);
		$newOpts['maxItemLength'] = max(
			floor($options['maxItemLength'] * $options['decayRate']),
			3
		);
		$newOpts['maxRetries'] = $options['maxRetries'] - 1;
		return $newOpts;
	}

	/**
	 * Recursively update $value by reference to shrink values
	 * @param mixed $value  The value to update
	 * @param array $options  Options as defined in static::$defaults
	 * @return array|string  The new value
	 */
	protected static function _walk($value, array $options = []) {
		if (is_string($value)) {
			// leave 2 characters for quotes
			$max = $options['maxItemLength'] - mb_strlen($options['ellipsis']) - 2;
			if (strlen($value) < $max) {
				return $value;
			}
			if ($options['ellipsis']) {
				// add 3 to overage for quotes and 1-char string
				$overage = mb_strlen($value) - $max + 3;
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
			if ($options['ellipsis']) {
				$ellipsis = str_replace(
					'%overage%',
					count($keysToRemove),
					$options['ellipsis']
				);
				$value[count($value)] = $ellipsis;
			}
		}
		return $value;
	}
}
