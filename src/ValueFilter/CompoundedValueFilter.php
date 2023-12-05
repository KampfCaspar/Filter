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
 * Compounded ValueFilter - applies several ValueFilters in one go
 */
class CompoundedValueFilter extends ValueFilter
{

	public const DEFAULT_OPTIONS = [
		self::OPTION_COMPOUNDED_FILTERS => [],
	] + parent::DEFAULT_OPTIONS;

	/**
	 * @stub
	 * @codeCoverageIgnore
	 */
	protected function doFilterValue(mixed $value): mixed
	{
		return $value;
	}

	public function filterValue(mixed $value): mixed
	{
		foreach ($this->options[self::OPTION_COMPOUNDED_FILTERS] as &$filter) {
			try {
				$filter = self::createFilter($filter, null, $this->options);
			}
			catch (\LogicException $e) {
				throw new OptionsException('could not get daughter filter', $e->getCode(), $e);
			}
			$value = $filter->filterValue($value);
		}
		return $value;
	}

}