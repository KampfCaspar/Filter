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
 * Value Filter Restricting to a List of Valid Values
 */
class AllowedValuesFilter extends ValueFilter
{
	/**
	 * Option Name For an Array of Mixed Values - only those exact values are accepted
	 */
	final const OPTION_WHITELIST = '_whitelist';

	public const DEFAULT_OPTIONS = [
		self::OPTION_WHITELIST => [],
	] + parent::DEFAULT_OPTIONS;

	protected function filterIndividualValue(mixed $value): mixed
	{
		if (!in_array($value, (array)$this->options[self::OPTION_WHITELIST], true)) {
			$value = $this->handleIllegalValue($value);
		}
		return $value;
	}
}