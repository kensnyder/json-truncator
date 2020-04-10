<?php

namespace KenSnyder;

class JsonTruncator {

	public static $defaults = [
		'maxLength' => 40000,
		'maxItems' => 100,
		'maxItemLength' => 8000,
		'decay' => 0.5,
	];

	public static function stringify($value, array $options = []) : string {
		$options = array_merge(static::$defaults, $options);
		$json = json_encode($value);
		if (strlen($json) <= $options['maxLength']) {
			return $json;
		}
		if (is_string($value)) {
			$shorter = mb_substr($value, 0, $options['maxLength'] - 2);
			return json_encode($shorter);
		}
		static::_walk($value, $options);
		return json_encode($value);
	}

	public static function _walk(&$value, array $options = []) {
		if (is_string($value)) {
			$value = mb_substr($value, 0, $options['maxItemLength'] - 2);
			return;
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
			static::_walk($item, $options);
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
	}

}
