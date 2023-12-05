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

use KampfCaspar\Filter\Exception\OptionsException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Base Interface For All Filter Types
 *
 * There are {@see ValueFilterInterface} and {@see ArrayFilterInterface}.
 * Both filter types can be fully configured by an array of options.
 */
interface FilterInterface extends LoggerAwareInterface
{
	/**
	 * common constructor
	 * @param array<string,mixed>|null $options
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(?array $options = null, ?LoggerInterface $logger = null);

	/**
	 * set filter options
	 *
	 * @param array<string,mixed> $options
	 * @return $this
	 * @throws OptionsException  on unknown option
	 */
	public function setOptions(array $options): static;

}