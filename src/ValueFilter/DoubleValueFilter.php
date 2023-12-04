<?php declare(strict_types=1);
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Filter\ValueFilter;

use KampfCaspar\Filter\ValueFilter;

/**
 * ValueFilter for Double Values
 */
class DoubleValueFilter extends ValueFilter
{
	/**
	 * Option Name for a Number - limiting filtered values to the bottom
	 *
	 * The minimum does *not* apply to {@see self::OPTION_DEFAULT} values.
	 */
	final const OPTION_MIN = '_min';

	/**
	 * Option Name for a Number - limiting filtered values to the top
	 *
	 * The maximum value does *not* apply to {@see self::OPTION_DEFAULT} values.
	 */
	final const OPTION_MAX = '_max';

	public const DEFAULT_OPTIONS = [
		self::OPTION_MIN => null,
		self::OPTION_MAX => null,
		self::OPTION_FORMATS => [
			'/^(?:-\\+)?(?:[0-9]+(?:\\.[0-9]*)?|\\.[0-9]*)$/'
		],
	] + parent::DEFAULT_OPTIONS;

	protected function convertValue(mixed $value): int|float|string
	{
		// we accept only numbers - everything else will be converted to string
		return is_int($value) || is_double($value) ? $value : (string)$value;
	}

	/**
	 * Extract Number From Value
	 */
	protected function extractNumber(mixed $value): int|float
	{
		return doubleval($value);
	}

	protected function doFilterValue(mixed $value): mixed
	{
		$value = $this->extractNumber($value);
		$min = $this->options[self::OPTION_MIN];
		if (!is_null($min) && $min > $value) {
			$value = $min;
		}
		$max = $this->options[self::OPTION_MAX];
		if (!is_null($max) && $max < $value) {
			$value = $max;
		}
		return $value;
	}

}