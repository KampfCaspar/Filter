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

class CallableValueFilter extends ValueFilter
{

	public const DEFAULT_OPTIONS = [
		self::OPTION_CALLABLE => null,
	] + parent::DEFAULT_OPTIONS;

	protected function doFilterValue(mixed $value): mixed
	{
		$callable = $this->options[self::OPTION_CALLABLE];
		/** @var mixed $old_value */
		$old_value = $value;
		$value = $callable($value, $this);
		if (is_null($value)) {
			$this->handleIllegalValue($old_value);
		}
		return $value;
	}
}