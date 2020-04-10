<?php

namespace KenSnyder;

class JsonTruncator {

	public static $defaults = [
		'maxLength' => 40000,
		'maxItems' => 100,
		'maxItemLength' => 8000,
		'decayRate' => 0.75,
		'maxAttempts' => 4,
	];

	public static function stringify($value, array $options = []) : string {
		$options = array_merge(static::$defaults, $options);
		// TODO: validate options
		if (
			!is_float($options['decayRate']) ||
			$options['decayRate'] <= 0 ||
			$options['decayRate'] >= 1
		) {
			throw new \Exception('decayRate must be a number between 0 and 1');
		}
		if (
			$options['maxLength'] < $options['maxItemLength']
		) {
			throw new \Exception('maxItemLength must less or equal to than maxLength');
		}
		return static::_attempt($value, $options);
	}

	public static function _attempt($value, array $options) {
		$json = json_encode($value);
		if (strlen($json) <= $options['maxLength']) {
			return $json;
		}
		$value = static::_walk($value, $options);
		$newOptions = static::_decay($options);
		if ($newOptions['maxAttempts'] === 0) {
			return substr(json_encode($value), 0, $options['maxLength']);
		}
		return static::_attempt($value, $newOptions);
	}

	public static function _decay(array $options) : array {
		return [
			'maxLength' => $options['maxLength'],
			'maxItems' => floor($options['maxItems'] * $options['decayRate']) ?: 1,
			'maxItemLength' => floor($options['maxItemLength'] * $options['decayRate']) ?: 3,
			'maxAttempts' => $options['maxAttempts'] - 1,
			'decayRate' => $options['decayRate'],
		];
	}

	public static function _walk(&$value, array $options = []) {
		if (is_string($value)) {
			return mb_substr($value, 0, $options['maxItemLength'] - 2);
		}
		if (is_object($value)) {
			$value = get_object_vars($value);
		}
		if (!is_array($value)) {
			return false;
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
