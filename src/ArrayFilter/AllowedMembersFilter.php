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
 * Array Filter Checking ArrayAccess Key Names
 *
 * Check an ArrayAccess instance for missing keys ({@see self::OPTION_MANDATORY})
 * and unexpected keys ({@see self::OPTION_OPTIONAL}).
 */
class AllowedMembersFilter extends ArrayFilter
{
	/**
	 * Option Name For an Array of Keys - on filtering, missing keys will fail
	 */
	final const OPTION_MANDATORY = 'mandatory';

	/**
	 * Option Name for an Array of Keys - on filtering, unlisted keys will fail
	 */
	final const OPTION_OPTIONAL = 'optionals';

	public const DEFAULT_OPTIONS = [
		self::OPTION_MANDATORY => [],
		self::OPTION_OPTIONAL => [],
	] + parent::DEFAULT_OPTIONS;

	public function filterArray(\ArrayAccess|array &$object): array
	{
		$errors = [];
		$keys = $this->getKeys($object);
		$missing = array_diff((array)$this->options[self::OPTION_MANDATORY], $keys);
		if ($missing) {
			$errors[] = $this->handleError('missing fields: ' . join(', ', $missing));
		}
		$surplus = array_diff($keys,
			(array)$this->options[self::OPTION_MANDATORY],
			(array)$this->options[self::OPTION_OPTIONAL]);
		if ($surplus) {
			$clear = $this->options[self::OPTION_CORRECT];
			$errors[] = $this->handleError(
				'surplus fields: ' . join(', ', $surplus),
				$clear);
			if ($clear) {
				foreach ($surplus as $one) {
					unset($object[$one]);
				}
			}
		}
		return $errors;
	}
}