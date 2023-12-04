<?php declare(strict_types=1);
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Test\Filter;

use Beste\Psr\Log\TestLogger;
use KampfCaspar\Filter\ValueFilter;
use KampfCaspar\Filter\ValueFilterInterface;
use KampfCaspar\Test\Filter\Stubs\NullArrayFilterStub;
use KampfCaspar\Test\Filter\Stubs\NullValueFilterStub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ValueFilterTest extends TestCase
{
	public function testScalarity(): void
	{
		$filter = new NullValueFilterStub();
		$filter->setOptions([
			ValueFilter::OPTION_SCALARITY => 'scalar'
		]);
		self::assertEquals(3, $filter->filterValue(3));
		$filter->setOptions([
			ValueFilter::OPTION_SCALARITY => 'array'
		]);
		self::assertEquals([3], $filter->filterValue(3));
		$filter->setOptions([
			ValueFilter::OPTION_SCALARITY => 'wrong'
		]);
		self::expectException(\BadMethodCallException::class);
		self::assertEquals(3, $filter->filterValue(3));
	}

	public function testSetLogger(): void
	{
		$mock = $this->createMock(LoggerInterface::class);
		self::expectException(\Exception::class);
		$filter = new NullValueFilterStub([], $mock);
	}

	public function testOptionsError(): void
	{
		$filter = new NullValueFilterStub();
		self::expectException(\BadMethodCallException::class);
		$filter->setOptions([
			'wrong option' => 'wrong option',
		]);
	}

	public function testCreateFilter(): void
	{
		self::assertInstanceOf(
			ValueFilterInterface::class,
			ValueFilter::createFilter(NullValueFilterStub::class)
		);
		self::assertInstanceOf(
			ValueFilterInterface::class,
			ValueFilter::createFilter([ValueFilter::OPTION_FILTER => NullValueFilterStub::class])
		);
		self::expectException(\BadMethodCallException::class);
		ValueFilter::createFilter(NullArrayFilterStub::class);
	}

	public function testCreateFilterIllegalSpec(): void
	{
		self::expectException(\DomainException::class);
		ValueFilter::createFilter([3]);
	}

	public function testCreateFilterWithParent(): void
	{
		$logger = TestLogger::create();
		$parent = new NullValueFilterStub([
			NullValueFilterStub::OPTION_NULL => 'a',
			NullValueFilterStub::OPTION_SOFT_FAILURE => 'b',
		]);
		/** @var ValueFilter $filter */
		$filter = ValueFilter::createFilter(NullValueFilterStub::class, null, $parent->_getOptions());
		self::assertEquals('a', $filter->_getOptions()[NullValueFilterStub::OPTION_NULL]);
		self::assertEquals('b', $filter->_getOptions()[NullValueFilterStub::OPTION_SOFT_FAILURE]);

		try {
			$parent->setLogger($logger);
		}
		catch (\Throwable) {}
		self::expectException(\Exception::class);
		ValueFilter::createFilter(NullValueFilterStub::class, $logger, $parent->_getOptions());
	}


}
