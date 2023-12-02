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
use KampfCaspar\Filter\CompoundedFilterTrait;
use KampfCaspar\Filter\ValueFilterInterface;

/**
 * Compounded ValueFilter - applies several ValueFilters in one go
 */
class CompoundedValueFilter extends ValueFilter
{
	use CompoundedFilterTrait;

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
			$filter = self::createFilter($filter, $this);
			if ($filter instanceof ValueFilterInterface) {
				$value = $filter->filterValue($value);
			}
			elseif (is_callable($filter)) {
				$value = $filter($value);
			}
			else {
				// @codeCoverageIgnoreStart
				throw new \LogicException('Universe Segfault');
				// @codeCoverageIgnoreEnd
			}
		}
		return $value;
	}

}