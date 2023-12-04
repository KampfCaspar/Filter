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
 * Value Filter Checking Against Perl Regular Expressions
 */
class PregValueFilter extends ValueFilter
{
	/**
	 * Option Name For an Array of Perl Regular Expressions - values are checked against those
	 */
	final const OPTION_PREGS = '_pregs';

	public const DEFAULT_OPTIONS = [
		self::OPTION_PREGS => null,
	] + parent::DEFAULT_OPTIONS;

	protected function convertValue(mixed $value): string
	{
		return strval($value); // we deal ONLY in strings
	}

	public function doFilterValue(mixed $value): mixed
	{
		$preg = (array)$this->options[self::OPTION_PREGS];
		if (!$preg) {
			throw new \BadMethodCallException('must set one or more perl regular expressions');
		}
		foreach ($preg as $one) {
			if (!is_string($one)) {
				throw new \BadMethodCallException('perl regular expression must be string');
			}
			$match = @preg_match($one, $value);
			if ($match === false) {
				throw new \BadMethodCallException('perl regex error: ' . preg_last_error_msg());
			}
			if ($match > 0) {
				return $value;
			}
		}
		return $this->handleIllegalValue($value);
	}

}