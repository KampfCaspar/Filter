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

/**
 * Interface for Value Filters
 *
 * Value filters accept a mixed value, can validate and transform it and
 * will return a correct value or null. Strict Value Filters will throw
 * {@see \InvalidArgumentException} on uncorrectable values
 */
interface ValueFilterInterface extends FilterInterface
{
	/**
	 * check value and either return it in its proper form or throw
	 *
	 * @return mixed                      correct value
	 * @throws \InvalidArgumentException  if value correction is unfeasable
	 */
	public function filterValue(mixed $value): mixed;
}