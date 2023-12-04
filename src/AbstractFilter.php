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
	 * Option Name For a Callable - only used for callable wrappers
	 */
	final const OPTION_CALLABLE = 'callable';

	/**
	 * Option Name For an Array of Filter Specifications - only used for compounded filters
	 */
	final const OPTION_COMPOUNDED_FILTERS = 'filters';

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

	protected const CALLABLE_WRAPPER = '';

	/**
	 * instantiate a filter from a specification
	 *
	 * The filter specification may be:
	 *   * a class name for a {@see FilterInterface} or callable class
	 *   * an option array defining a {@see self::OPTION_FILTER}
	 *   * a pre-existing {@see FilterInterface}
	 *   * a callable
	 *
	 * @param array<mixed>|string|FilterInterface|callable $filter
	 * @param array<string,mixed>                          $parentOptions
	 */
	protected static function instantiate(
		mixed $filter,
		?LoggerInterface $logger = null,
		array $parentOptions = [],
	): FilterInterface
	{
		$options = [];
		$class = null;
		if (is_callable($filter)) {
			if ($filter instanceof LoggerAwareInterface && $logger) {
				$filter->setLogger($logger);
			}
			$class = static::CALLABLE_WRAPPER;
			$options = [
				self::OPTION_CALLABLE => $filter,
			];
		}
		elseif (is_string($filter)) {
			$class = $filter;
		}
		elseif (
			is_array($filter) && array_is_list($filter) && count($filter) === 2
			&& is_string($filter[0]) && is_array($filter[1])
		) {
			$class = $filter[0];
			$options = $filter[1];
		}
		elseif (is_array($filter) && isset($filter[self::OPTION_FILTER])) {
			$class = $filter[self::OPTION_FILTER];
			$options = $filter;
			/** @var FilterInterface $filter */
			$filter = new ($filter[self::OPTION_FILTER])($filter);
		}

		if ($class) {
			$filter = new $class();
		}
		if (!$filter instanceof FilterInterface) {
			throw new \DomainException('unknown filter specification');
		}

		if ($logger) {
			$filter->setLogger($logger);
		}
		$filter->setOptions(
			$options +
			array_intersect_key($parentOptions, array_flip(static::INHERIT_OPTIONS))
		);

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
	 * @internal
	 * @return array<string,mixed>
	 */
	public function _getOptions(): array
	{
		return $this->options;
	}

}