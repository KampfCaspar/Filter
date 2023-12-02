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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Filter Base Class
 *
 * All filters inherit from here. We establish a common
 * {@see self::$options} array and a constant for
 * {@see self::DEFAULT_OPTIONS}.
 *
 * All option keys are listed as a final const beginning with
 * OPTION_*.
 */
abstract class AbstractFilter implements FilterInterface
{
	use LoggerAwareTrait;

	/**
	 * Option Name For a String - giving the filter a name, primarily for error messages
	 */
	final const OPTION_NAME = 'name';

	/**
	 * Option Name For a Bool - switching off exception throwing
	 */
	final const OPTION_SOFT_FAILURE = 'soft';

	/**
	 * Option Name For a String - used to instantiate object in filter creation
	 * @see ValueFilter::createFilter()
	 * @see ArrayFilter::createFilter()
	 */
	final const OPTION_FILTER = 'filter';

	/**
	 * Default Options
	 */
	public const DEFAULT_OPTIONS = [
		self::OPTION_NAME => null,
		self::OPTION_SOFT_FAILURE => false,
		self::OPTION_FILTER => null,
	];

	/**
	 * Array of option names that will be inherited filter creation
	 * @see ValueFilter::createFilter()
	 * @see ArrayFilter::createFilter()	 */
	protected const INHERIT_OPTIONS = [
		self::OPTION_SOFT_FAILURE,
	];

	/**
	 * instantiate a filter from a specification
	 *
	 * The filter specification may be:
	 *   * a class name for a {@see FilterInterface} or callable class
	 *   * an option array defining a {@see self::OPTION_FILTER}
	 *   * a pre-existing {@see FilterInterface}
	 *   * a callable
	 *
	 * @param mixed[]|string|FilterInterface|callable $filter
	 */
	protected static function instantiate(
		mixed $filter,
		LoggerInterface|AbstractFilter|null $parent = null,
	): FilterInterface|callable
	{
		if (is_string($filter)) {
			/**
			 * @var FilterInterface|callable $filter
			 */
			$filter = new $filter();
		}
		elseif (is_array($filter) && isset($filter[self::OPTION_FILTER])) {
			/**
			 * @var FilterInterface $filter
			 */
			$filter = new ($filter[self::OPTION_FILTER])($filter);
		}
		elseif (!is_callable($filter) && !$filter instanceof FilterInterface) {
			throw new \DomainException('unknown filter specification');
		}

		// @phpstan-ignore-next-line because you somehow ignore the type check
		$logger = ($parent instanceof FilterInterface) ? $parent->logger : $parent;
		if ($logger && ($filter instanceof LoggerAwareInterface)) {
			// @phpstan-ignore-next-line because you somehow ignore the type check
			$filter->setLogger($parent->logger);
		}
		if (($filter instanceof FilterInterface) && ($parent instanceof FilterInterface)) {
			$filter->setOptions(
				// @phpstan-ignore-next-line because you somehow ignore the type check
				array_intersect_key($parent->options, array_flip(static::INHERIT_OPTIONS))
			);
		}

		return $filter;
	}

	/**
	 * Instance Option Array
	 * @var array<string,mixed>
	 */
	protected array $options;

	/**
	 * Common Constructor of all Filters
	 *
	 * @param array<string,mixed>|null $options
	 * @param LoggerInterface|null $logger
	 */
	public function __construct(?array $options = null, ?LoggerInterface $logger = null)
	{
		if ($logger) {
			$this->setLogger($logger);
		}
		$this->options = static::DEFAULT_OPTIONS;
		if ($options) {
			$this->setOptions($options);
		}
	}

	public function setOptions(array $options): static
	{
		if (($unknown = array_diff_key($options, $this->options))) {
			throw new \BadMethodCallException(sprintf(
				'unknown filter option(s) "%s"',
				join('", "', $unknown),
			));
		}
		$this->options = $options + $this->options;
		return $this;
	}

	/**
	 * @return mixed[]
	 * @internal
	 */
	public function _getOptions(): array
	{
		return $this->options;
	}

}