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

use KampfCaspar\Filter\ValueFilter\CallableValueFilter;
use Psr\Log\LoggerInterface;

/**
 * Value Filter Base class
 */
abstract class ValueFilter extends AbstractFilter implements ValueFilterInterface
{
	/**
	 * Option Name For an Enum - indicating if scalar and/or array values are accepted
	 *
	 * Values may be:
	 *   * 'scalar': only scalar values are accepted
	 *   * 'array': only an array of values is accepted - scalar values are silently converted
	 *   * null: accept any
	 */
	final const OPTION_SCALARITY = 'scalarity';

	/**
	 * Option Name For Mixed - the default value in case no/empty values are filtered
	 *
	 * The default value is silently converted to scalar/array scalarity.
	 */
	final const OPTION_DEFAULT = 'default';

	/**
	 * Option Name For a Bool - indicating whether null values are accepted
	 */
	final const OPTION_NULL = 'null';

	/**
	 * Option Name For a Perl Regex - matches are deleted from string values
	 * @see self::convertValue()
	 */
	final const OPTION_CLEAN = 'clean';

	/**
	 * Option Name For an Array of Perl Regexes - format checkers for string values
	 * @see self::convertValue()
	 */
	final const OPTION_FORMATS = 'formats';

	public const DEFAULT_OPTIONS = [
		self::OPTION_SCALARITY => 'scalar',        // only accept scalar values
		self::OPTION_DEFAULT => null,
		self::OPTION_NULL => false,                // do NOT accept null values
		self::OPTION_CLEAN => '/(?:^\\s+|\\s+$)/', // meaning: trim string values
		self::OPTION_FORMATS => [],
	] + parent::DEFAULT_OPTIONS;

	protected const INHERIT_OPTIONS = [
		self::OPTION_SOFT_FAILURE, self::OPTION_NULL
	];

	protected const CALLABLE_WRAPPER = CallableValueFilter::class;

	/**
	 * Value Filter Creator
	 * @see self::instantiate()
	 *
	 * @param array<mixed>|string|FilterInterface|callable $filter
	 * @param array<string,mixed> $parentOptions
	 */
	public static function createFilter(
		mixed $filter,
		?LoggerInterface $logger = null,
		array $parentOptions = [],
	): ValueFilterInterface
	{
		$res = static::instantiate($filter, $logger, $parentOptions);
		if (!$res instanceof ValueFilterInterface) {
			throw new \BadMethodCallException('could not instantiate ValueFilter');
		}
		return $res;
	}

	/**
	 * Handle Illegal Values According to Options
	 */
	protected function handleIllegalValue(mixed $value): null
	{
		$filter = $this->options[self::OPTION_NAME] ?? get_class($this);
		$value_str = is_scalar($value) ? (string)$value : gettype($value);
		if (!$this->options[self::OPTION_SOFT_FAILURE]) {
			throw new \InvalidArgumentException(sprintf(
				'illegal value for filter %s: %s',
				$filter,
				$value_str,
			));
		}
		$this->logger?->info('suppressed illegal value for {filter}: {value}', [
			'filter' => $filter,
			'value' => $value_str,
		]);
		return null;
	}

	/**
	 * Ensure Correct Scalarity of Value
	 */
	private function preFilterScalarity(mixed $value): mixed
	{
		$type = $this->options[self::OPTION_SCALARITY];
		if ($type === 'scalar') {
			if (!is_scalar($value)) {
				$value = $this->handleIllegalValue($value);
			}
		}
		elseif ($type === 'array') {
			if (!is_array($value)) {
				$value = [ $value ];
			}
		}
		elseif (!is_null($type)) {
			throw new \BadMethodCallException(sprintf(
				'unknown scalarity value: %s',
				$type,
			));
		}
		return $value;
	}

	/**
	 * Convert Value to Usable Type
	 *
	 * Values can first be converted to a usable type, e.g. string.
	 * String values are later checked with {@see self::OPTION_FORMATS}.
	 *
	 * @see self::preFilterFormats()
	 */
	protected function convertValue(mixed $value): mixed
	{
		return $value;
	}

	/**
	 * Ensure a Value Conforms to Common Format Criteria
	 */
	private function preFilterFormats(mixed $value): mixed
	{
		if (is_null($value) && !$this->options[self::OPTION_NULL]) {
			return $this->handleIllegalValue($value);
		}
		$value = $this->convertValue($value);
		if (!is_string($value)) {
			return $value;
		}
		$value = preg_replace($this->options[self::OPTION_CLEAN], '', $value);
		if (!$this->options[self::OPTION_FORMATS]) {
			return $value;
		}
		foreach ($this->options[self::OPTION_FORMATS] as $format) {
			$format = (array)$format;
			$count = 0;
			// @phpstan-ignore-next-line as you ignore the prior type check
			$value = preg_replace($format[0], $format[1] ?? '$0', $value, -1, $count);
			if ($count) {
				return $value;
			}
		}
		return $this->handleIllegalValue($value);
	}

	/**
	 * Ensure Default Values Are Substituted In
	 */
	private function postFilterDefault(mixed $value): mixed
	{
		if (is_array($value)) {
			$value = array_filter($value, fn($x) => !is_null($x));
		}

		$default = $this->options[self::OPTION_DEFAULT];
		if (is_null($default)) {
			return $value;
		}

		// @phpstan-ignore-next-line as your warnings are misguided
		if (is_null($value) || (is_array($value) && count($value)===0)) {
			$value = $default;
			if (is_scalar($value) && $this->options[self::OPTION_SCALARITY] == 'array') {
				$this->logger?->warning('scalar default value "{value}" forced to array', [
					'value' => $value,
				]);
				$value = (array)$value;
			}
			if (is_array($value) && $this->options[self::OPTION_SCALARITY] == 'scalar') {
				$first = reset($value);
				$this->logger?->warning('array default value forced to its first element "{first}"', [
					'first' => $first,
					'value' => $value,
				]);
				$value = $first;
			}
		}
		return $value;
	}

	/**
	 * Actually Filter a Single Value
	 */
	abstract protected function doFilterValue(mixed $value): mixed;

	public function filterValue(mixed $value): mixed
	{
		$value = $this->preFilterScalarity($value);
		if (is_array($value)) {
			foreach ($value as &$one) {
				$one = $this->preFilterFormats($one);
				if (!is_null($one)) {
					$one = $this->doFilterValue($one);
				}
			}
		}
		else {
			$value = $this->preFilterFormats($value);
			if (!is_null($value)) {
				$value = $this->doFilterValue($value);
			}
		}
		return $this->postFilterDefault($value);
	}

}