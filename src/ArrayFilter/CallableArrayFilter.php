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

class CallableArrayFilter extends ArrayFilter
{

	public const DEFAULT_OPTIONS = [
		self::OPTION_CALLABLE => null,
	] + parent::DEFAULT_OPTIONS;

	public function filterArray(\ArrayObject|array|\ArrayIterator &$object): array
	{
		$callable = $this->options[self::OPTION_CALLABLE];
		$result = $callable($object, $this);
		if ($result) {
			$this->handleError($result[0]);
		}
		return $result;
	}
}