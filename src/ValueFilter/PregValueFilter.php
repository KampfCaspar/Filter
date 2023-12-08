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
 * Value Filter Checking Against Perl Regular Expressions
 */
class PregValueFilter extends ValueFilter
{

	protected function convertValue(mixed $value): ?string
	{
		if (!$this->options[self::OPTION_PREG]) {
			throw new OptionsException(sprintf(
				'%s filter without mandatory preg',
				$this->getName()
			));
		}
		if (is_scalar($value) || $value instanceof \Stringable) {
			return strval($value); // we deal ONLY in strings
		}
		return $this->handleIllegalValue($value);
	}

	protected function filterIndividualValue(mixed $value): mixed
	{
		// we just use the perl regex in pre-filtering
		return $value;
	}

}