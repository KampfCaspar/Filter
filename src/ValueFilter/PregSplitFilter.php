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

use KampfCaspar\Filter\Exception\OptionsException;
use KampfCaspar\Filter\ValueFilter;

/**
 * Value Filter Splitting Strings With a Perl Regex Into Arrays
 */
class PregSplitFilter extends ValueFilter
{
	/**
	 * Option Name For a Perl Regular Expression - used to split the strings
	 */
	final const OPTION_SPLIT = '_split';

	/**
	 * Option Name For an Int - defining the maximum parts to split into
	 */
	final const OPTION_LIMIT = '_limit';

	public const DEFAULT_OPTIONS = [
		self::OPTION_SCALARITY => 'scalar',
		self::OPTION_SPLIT => '/\\s+/u',
		self::OPTION_LIMIT => -1,
	] + parent::DEFAULT_OPTIONS;

	protected function convertValue(mixed $value): string
	{
		if (is_scalar($value) || $value instanceof \Stringable) {
			return strval($value); // we deal ONLY in strings
		}
		else {
			return $this->handleIllegalValue($value);
		}
	}

	public function filterIndividualValue(mixed $value): mixed
	{
		$preg = $this->options[self::OPTION_SPLIT];
		if (!is_string($preg)) {
			throw new OptionsException(sprintf(
				'%s filter split preg must be string, got %s',
				$this->getName(),
				gettype($preg)
			));
		}
		$value = @preg_split($preg, $value, $this->options[self::OPTION_LIMIT]);
		if ($value === false) {
			throw new OptionsException(sprintf(
				'%s filter preg error: %s',
				$this->getName(),
				preg_last_error_msg()
			));
		}
		return $value;
	}

}