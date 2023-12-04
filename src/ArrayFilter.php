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

use KampfCaspar\Filter\ArrayFilter\CallableArrayFilter;
use Psr\Log\LoggerInterface;

/**
 * Array Filter Base Class
 */
abstract class ArrayFilter extends AbstractFilter implements ArrayFilterInterface
{
	/**
	 * Option Name For a Bool - enabling changing the ArrayAccess instance for validity
	 */
	final const OPTION_CORRECT = '_correct';

	public const DEFAULT_OPTIONS = [
		self::OPTION_CORRECT => false,
	] + parent::DEFAULT_OPTIONS;

	protected const INHERIT_OPTIONS = [
		self::OPTION_SOFT_FAILURE, self::OPTION_CORRECT
	];

	protected const CALLABLE_WRAPPER = CallableArrayFilter::class;

	/**
	 * Array Filter Creator
	 *
	 * @param array<string,mixed> $parentOptions
	 * @param array<mixed>|string|FilterInterface|callable $filter
	 * @see self::instantiate()
	 */
	public static function createFilter(
		mixed $filter,
		?LoggerInterface $logger = null,
		array $parentOptions = [],
	): ArrayFilterInterface
	{
		$res = static::instantiate($filter, $logger, $parentOptions);
		if (!$res instanceof ArrayFilterInterface) {
			throw new \BadMethodCallException('could not instantiate ArrayFilter');
		}
		return $res;
	}

	/**
	 * Get All Keys
	 * @param \ArrayObject<string,mixed>|\ArrayIterator<string,mixed>|array<string,mixed> $object
	 * @return string[]
	 */
	protected function getKeys(\ArrayObject|\ArrayIterator|array $object): array
	{
		if (is_array($object)) {
			$keys = array_keys($object);
		}
		else {
			$keys = array_keys($object->getArrayCopy());
		}
		return $keys;
	}

	/**
	 * Handle an Error
	 */
	protected function handleError(string $error, bool $softFail = false): string
	{
		$filter = $this->options[self::OPTION_NAME] ?: get_class($this);
		if (!$this->options[self::OPTION_SOFT_FAILURE] && !$softFail) {
			throw new \DomainException(sprintf(
				'filter %s failure: %s',
				$filter,
				$error,
			));
		}
		$this->logger?->info('suppressed filter {filter} failure: {error}', [
			'filter' => $filter,
			'error' => $error,
		]);
		return $error;
	}

}