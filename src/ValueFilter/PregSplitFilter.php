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
 * Value Filter Splitting Strings With a Perl Regex Into Arrays
 */
class PregSplitFilter extends ValueFilter
{
	/**
	 * Option Name For a Perl Regular Expression - used to split the strings
	 */
	final const OPTION_SPLIT = 'split';

	/**
	 * Option Name For an Int - defining the maximum parts to split into
	 */
	final const OPTION_LIMIT = 'limit';

	public const DEFAULT_OPTIONS = [
		self::OPTION_SCALARITY => 'scalar',
		self::OPTION_SPLIT => '/\\s+/u',
		self::OPTION_LIMIT => -1,
	] + parent::DEFAULT_OPTIONS;

	protected function convertValue(mixed $value): string
	{
		return strval($value); // we deal ONLY in strings
	}

	public function doFilterValue(mixed $value): mixed
	{
		$preg = $this->options[self::OPTION_SPLIT];
		if (!is_string($preg)) {
			throw new \BadMethodCallException('perl regular expression must be string');
		}
		$value = @preg_split($preg, $value, $this->options[self::OPTION_LIMIT]);
		if ($value === false) {
			throw new \BadMethodCallException('perl regex error: ' . preg_last_error_msg());
		}
		return $value;
	}

}