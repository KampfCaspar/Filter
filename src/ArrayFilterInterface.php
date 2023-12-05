<?php declare(strict_types=1);
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Filter;

use KampfCaspar\Filter\Exception\FilteringException;

/**
 * Filter for ArrayAccess Instances
 *
 * Array Filters do not check only a value but the 'inner consistency' of ArrayAccess
 * instances. They can alter the instance to achieve validity.
 * Strict Array Filters will throw {@see FilteringException} if there is an error,
 * forgiving Array Filters will return an array of string error messages.
 */
interface ArrayFilterInterface extends FilterInterface
{
	/**
	 * check the validity of the ArrayAccess instance, change it for compliance or throw
	 *
	 * @param \ArrayObject<string,mixed>|\ArrayIterator<string,mixed>|array<string,mixed> $object
	 * @return string[]            collected error messages
	 * @throws FilteringException  if compliance is unfeasible
	 */
	public function filterArray(\ArrayObject|\ArrayIterator|array &$object): array;
}