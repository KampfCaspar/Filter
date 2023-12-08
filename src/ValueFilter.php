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
use KampfCaspar\Filter\Exception\OptionsException;
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
	 *   * 'not-list': accept scalar values and objects
	 *   * 'list': only a linear array of values is accepted - all other values are silently converted
	 *   * null: accept any
	 *
	 * The definition of scalarity differs from plain PHP as it includes NULL ({@see self::OPTION_NULL})
	 * and may include objects implementing {@see \Stringable}.
	 */
	final const OPTION_SCALARITY = '_scalarity';

	/**
	 * Option Name For a Bool - indicating whether null values are accepted
	 */
	final const OPTION_NULL = '_null';

	/**
	 * Option Name For a Bool - indicating if Stringable objects shall be converted to string
	 */
	final const OPTION_STRINGIFY = '_stringify';

	/**
	 * Option Name For Mixed - the default value in case no/empty values are filtered
	 *
	 * The default value is silently converted to scalar/array scalarity.
	 */
	final const OPTION_DEFAULT = '_default';

	/**
	 * Option Name For a Perl Regex - matches are deleted from string values
	 * @see self::convertValue()
	 */
	final const OPTION_CLEAN = '_clean';

	/**
	 * Option Name For an Array of Perl Regexes - format checkers for string values
	 * @see self::convertValue()
	 */
	final const OPTION_PREG = '_preg';

	public const DEFAULT_OPTIONS = [
		self::OPTION_SCALARITY => 'scalar',        // only accept scalar values
		self::OPTION_NULL => false,                // do NOT accept null values
		self::OPTION_STRINGIFY => true,            // DO accept \Stringable objects as scalar string
		self::OPTION_DEFAULT => null,
		self::OPTION_CLEAN => '/(?:^\\s+|\\s+$)/', // meaning: trim string values
		self::OPTION_PREG => [],
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
			throw new \InvalidArgumentException('could not instantiate ValueFilter');
		}
		return $res;
	}

	/**
	 * Get Filter Name
	 */
	protected function getName(): string
	{
		return $this->options[self::OPTION_NAME] ?? strrchr(get_class($this), '\\');
	}

	/**
	 * Handle Illegal Values According to Options
	 */
	protected function handleIllegalValue(mixed $value): null
	{
		$value_str = is_scalar($value) ? (string)$value : gettype($value);
		if (!$this->options[self::OPTION_SOFT_FAILURE]) {
			throw new FilteringException(sprintf(
				'illegal value for filter %s: %s',
				$this->getName(),
				$value_str,
			));
		}
		$this->logger?->info('suppressed illegal value for {filter}: {value}', [
			'filter' => $this->getName(),
			'value' => $value_str,
		]);
		return null;
	}

	/**
	 * Ensure Correct Scalarity of Value
	 */
	private function preFilterScalarity(mixed $value): mixed
	{
		if ($value instanceof \Stringable && $this->options[self::OPTION_STRINGIFY]) {
			$value = strval($value);
		}
		$type = $this->options[self::OPTION_SCALARITY];
		if ($type === 'scalar') {
			if (!is_scalar($value) && !is_null($value)) {
				$value = $this->handleIllegalValue($value);
			}
		}
		elseif ($type === 'not-list') {
			if (is_array($value) && array_is_list($value)) {
				$value = $this->handleIllegalValue($value);
			}
		}
		elseif ($type === 'list') {
			if (!is_array($value) || !array_is_list($value)) {
				$value = [$value];
			}
		}
		elseif (!is_null($type)) {
			throw new OptionsException(sprintf(
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
	 * String values are later checked with {@see self::OPTION_PREG}.
	 *
	 * @see self::preFilterValue()
	 */
	protected function convertValue(mixed $value): mixed
	{
		return $value;
	}

	/**
	 * Ensure a Value Conforms to Common Format Criteria
	 */
	private function preFilterValue(mixed $value): mixed
	{
		if (is_null($value)) {
			if (!$this->options[self::OPTION_NULL]) {
				$this->handleIllegalValue(null);
			}
			return null;
		}
		$value = $this->convertValue($value);
		if ($value instanceof \Stringable && $this->options[self::OPTION_STRINGIFY]) {
			$value = strval($value);
		}
		if (!is_string($value)) {
			return $value;
		}
		$value = preg_replace($this->options[self::OPTION_CLEAN], '', $value);
		if (!$this->options[self::OPTION_PREG]) {
			return $value;
		}
		foreach ((array)$this->options[self::OPTION_PREG] as $format) {
			$format = (array)$format;
			$count = 0;
			if (!is_string($format[0])) {
				throw new OptionsException(sprintf(
					'%s filter perl regular expression must be string, got %s',
					$this->getName(),
					gettype($format[0])
				));
			}
			// @phpstan-ignore-next-line as you ignore the prior type check
			$value = @preg_replace($format[0], $format[1] ?? '$0', $value, -1, $count);
			if (is_null($value)) {
				throw new OptionsException(sprintf(
					'%s filter perl regex error: %s',
					$this->getName(),
					preg_last_error_msg()
				));
			}
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
			$scalarity = $this->options[self::OPTION_SCALARITY];
			if (is_scalar($value) && $scalarity === 'list') {
				$this->logger?->warning('scalar default value "{value}" forced to list', [
					'filter' => $this->getName(),
					'value' => $value,
				]);
				$value = (array)$value;
			}
			if (is_array($value) && !is_null($scalarity) && $scalarity !== 'list') {
				$first = reset($value);
				$this->logger?->warning('array default value forced to its first element "{first}"', [
					'filter' => $this->getName(),
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
	abstract protected function filterIndividualValue(mixed $value): mixed;

	public function filterValue(mixed $value): mixed
	{
		$value = $this->preFilterScalarity($value);
		if (is_array($value) && array_is_list($value)) {
			foreach ($value as &$one) {
				$one = $this->preFilterValue($one);
				if (!is_null($one)) {
					$one = $this->filterIndividualValue($one);
				}
			}
		}
		else {
			$value = $this->preFilterValue($value);
			if (!is_null($value)) {
				$value = $this->filterIndividualValue($value);
			}
		}
		return $this->postFilterDefault($value);
	}

}