<?php

/**
 * A PHP class full of helpful utility functions for filtering, sorting, and otherwise manipulating arrays in PHP.
 * @author  Michael Fenwick <mike@mikefenwick.com>
 */
class ArrayUtil {

	/**
	 * Return keys from an array.
	 * Mimics the behaviour of PHP's built in array_keys function ({@link http://php.net/manual/en/function.array-keys.php}) except that it will recursively traverse the input array.
	 *
	 * @param array $array       The array from which to gather the keys
	 * @param null  $searchValue If specified, then only keys containing these values are returned.
	 * @param bool  $strict      Determines if strict comparison (===) should be used during the search.
	 *
	 * @return array An array with the keys from the input array.
	 */
	public static function arrayKeysRecursive(array $array, $searchValue = null, $strict = false) {
		$keyArray = array();
		array_walk_recursive($array, function ($value, $key) use (&$keyArray, $searchValue, $strict) {
			if ($searchValue === null) {
				$keyArray[] = $key;
			} else {
				if ($strict) {
					if ($value === $searchValue) {
						$keyArray[] = $key;
					}
				} else {
					if ($value == $searchValue) {
						$keyArray[] = $key;
					}
				}
			}
		});
		return $keyArray;
	}


	/**
	 * Recursively filters elements out of an array whose value, when run through the callback function, does not return true.
	 * Mimics the behaviour of PHP's built in array_filter function ({@link http://php.net/manual/en/function.array-filter.php}) except that it will recursively traverse the input array.
	 * Arrays inside the input array are filtered out BEFORE their internal elements are checked.
	 * Array keys of remaining elements are preserved.
	 *
	 * @param array    $array             The array to iterate over.
	 * @param callable $callback          The callback function to use.  If no callback is supplied, all entries of input with a boolean value of false will be removed.
	 * @param bool     $removeEmptyArrays Will remove any arrays that have no elements in them.  Used to clean up arrays whose elements have been filtered out by this function.
	 *
	 * @return array The filtered array.
	 */
	public static function arrayFilterRecursive(array $array, callable $callback = "", $removeEmptyArrays = true) {
		if ($callback === "") {
			$callback = function ($value) {
				return $value;
			};
		}
		if ($array) {
			foreach ($array as $key => $value) {
				if (!call_user_func($callback, $value)) {
					unset($array[$key]);
				} else {
					if (is_array($value)) {
						$array[$key] = self::arrayFilterRecursive($value, $callback, $removeEmptyArrays);
						if ($removeEmptyArrays) {
							if ($array[$key] === array()) {
								unset($array[$key]);
							}
						}
					}
				}
			}
		}
		return $array;
	}

	/**
	 * Flattens a multiple dimensional array into a single dimensional array.
	 * The input array's keys are preserved in the flattened array separated by the keySeparator.
	 * For the default keySeparator (underscore), the flattened array is such that $inputArray['i']['j]['k'] = $flattenedArray['i_j_k'].
	 * ArrayUtil::flatten and ArrayUtil::unflatten are inverse functions.
	 *
	 * @param array       $array        The array to be flattened.
	 * @param string      $keySeparator The string to be placed between the input array's keys.  Defaults to an underscore (_).
	 * @param string|null $keyPrefix    Array keys in the flattened array will be prefixed by this value.  Used for recursive calls.
	 *
	 * @return array The flattened array.
	 * @see unflatten To reverse the effects of this function
	 */
	public static function flatten(array $array, $keySeparator = "_", $keyPrefix = null) {
		$flattenedArray = array();
		foreach ($array as $key => $value) {
			$flattenedKey = ($keyPrefix ? $keyPrefix.$keySeparator : "").$key;
			if (is_array($value)) { //if this element is itself an array, recursively call this function and merge the results onto the flattenedArray.
				$flattenedArray = array_merge($flattenedArray, self::flatten($value, $keySeparator, ($keyPrefix ? $keyPrefix.$keySeparator : "").$key));
			} else { //otherwise just merge this value on the flattenedArray.
				$flattenedArray = array_merge($flattenedArray, array(($keyPrefix ? $keyPrefix.$keySeparator : "").$key => $value));
			}
		}
		return $flattenedArray;
	}

	/**
	 * Transposes an array.
	 * For arrays of dimension two or greater, returns a transposed array such that $inputArray[$i][$j] = $transposedArray[$j][$i].
	 * For arrays of one dimension, returns a transposed array such that $inputArray[$i] = $transposedArray[0][$i].
	 * Keys on the input array are maintained (relative to the transposition operation).
	 *
	 * @param array $array The array to be transposed.
	 *
	 * @return array The transposed array.
	 */
	public static function transpose(array $array) {
		$transposedArray = array();
		if ($array) {
			foreach ($array as $rowKey => $row) {
				if (is_array($row) && !empty($row)) { //check to see if there is a second dimension
					foreach ($row as $columnKey => $element) {
						$transposedArray[$columnKey][$rowKey] = $element;
					}
				} else {
					$transposedArray[0][$rowKey] = $row;
				}
			}
		}
		return $transposedArray;
	}

	/**
	 * Creates a multiple dimensional array from a single dimentional array.
	 * The unflattened array will have a structure given by the keys of the input array.
	 * For the default delimiter (underscore), the unflattened array is such that $inputArray['i_j_k'] = $unflattenedArray['i']['j']['k'].
	 * ArrayUtil::unflatten and ArrayUtil::flatten are inverse functions.
	 *
	 * @param array  $array     The array to unflatten.
	 * @param string $delimiter The delimiter on which the input array's keys will be exploded.
	 *
	 * @return array The unflattened array.
	 * @see flatten An inverse function.
	 */
	public static function unflatten(array $array, $delimiter = '_') {
		$unflattenedArray = array();
		foreach ($array as $key => $value) {
			$keyList = explode($delimiter, $key);
			$firstKey = array_shift($keyList);
			if (sizeof($keyList) > 0) { //does it go deeper, or was that the last key?
				$subarray = ArrayUtil::unflatten(array(implode($delimiter, $keyList) => $value), $delimiter);
				foreach ($subarray as $subarrayKey => $subarrayValue) {
					$unflattenedArray[$firstKey][$subarrayKey] = $subarrayValue;
				}
			} else {
				$unflattenedArray[$firstKey] = $value;
			}
		}
		return $unflattenedArray;
	}
}

