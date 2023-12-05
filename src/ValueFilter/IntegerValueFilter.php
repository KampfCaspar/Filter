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

/**
 * ValueFilter for Integer Values - including common bases
 */
class IntegerValueFilter extends DoubleValueFilter
{
	/**
	 * Short Names for Number Formats
	 */
	private const NUMBER_FORMATS = [
		'dec'  => '/^(?:[-+])?[1-9][0-9]*$/',
		'hex'  => '/^(?:[-+])?0x[0-9a-f]+$/i',
		'bin'  => '/^(?:[-+])?0b[01]+$/',
		'oct'  => '/^(?:[-+])?0[0-7]+$/',
		'octo' => ['/^([-+])?0o([0-7]+)$/', '${1}0$2'],
	];

	public const DEFAULT_OPTIONS = [
		self::OPTION_CLEAN => '/(?:^\\s+|[_\']|\\s+$)/',
		self::OPTION_PREG => self::NUMBER_FORMATS,
	] + parent::DEFAULT_OPTIONS;

	/**
	 * @inheritdoc
	 *
	 * As a special case, the {@see self::OPTION_PREG} option
	 * may reference the short names for number formats:
	 *   * dec: decimal
	 *   * hex: hexadecimal
	 *   * bin: binary
	 *   * oct: octal - classical format with a leading '0'
	 *   * octo: octal - modern format with a leading '0o'
	 */
	public function setOptions(array $options): static
	{
		if (isset($options[self::OPTION_PREG])) {
			$options[self::OPTION_PREG] = (array)$options[self::OPTION_PREG];
			$shorts = array_intersect_key(self::NUMBER_FORMATS, array_flip($options[self::OPTION_PREG]));
			$surplus = array_diff($options[self::OPTION_PREG], array_keys(self::NUMBER_FORMATS));
			$options[self::OPTION_PREG] = $shorts + $surplus;
		}
		return parent::setOptions($options);
	}

	protected function extractNumber(mixed $value): int|float
	{
		return intval($value, 0);
	}

}