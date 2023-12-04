<?php declare(strict_types=1);
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Filter\ArrayFilter;

use KampfCaspar\Filter\ArrayFilter;

/**
 * Compounded ArrayFilter - applies several ArrayFilters in one go
 */
class CompoundedArrayFilter extends ArrayFilter
{

	public const DEFAULT_OPTIONS = [
		self::OPTION_COMPOUNDED_FILTERS => [],
	] + parent::DEFAULT_OPTIONS;

	public function filterArray(\ArrayObject|array|\ArrayIterator &$object): array
	{
		$errors = [];
		foreach ($this->options[self::OPTION_COMPOUNDED_FILTERS] as &$filter) {
			$filter = self::createFilter($filter, null, $this->options);
			$errors += $filter->filterArray($object);
		}
		return $errors;
	}

}