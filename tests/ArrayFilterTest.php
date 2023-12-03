<?php
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
use KampfCaspar\Filter\ArrayFilter;
use KampfCaspar\Filter\ArrayFilterInterface;
use KampfCaspar\Filter\ValueFilter;
use KampfCaspar\Test\Filter\Stubs\NullArrayFilterStub;
use KampfCaspar\Test\Filter\Stubs\NullValueFilterStub;
use PHPUnit\Framework\TestCase;

class ArrayFilterTest extends TestCase
{
	public function testCreateFilter(): void
	{
		self::assertInstanceOf(
			ArrayFilterInterface::class,
			ArrayFilter::createFilter(NullArrayFilterStub::class)
		);
		self::assertInstanceOf(
			ArrayFilterInterface::class,
			ArrayFilter::createFilter([ArrayFilter::OPTION_FILTER => NullArrayFilterStub::class])
		);
		self::expectException(\BadMethodCallException::class);
		ArrayFilter::createFilter(NullValueFilterStub::class);
	}

	public function testCreateFilterWithParent(): void
	{
		$logger = TestLogger::create();
		$parent = new NullArrayFilterStub([
			NullArrayFilterStub::OPTION_CORRECT => 'a',
			NullArrayFilterStub::OPTION_SOFT_FAILURE => 'b',
		]);
		/** @var ArrayFilter $filter */
		$filter = ArrayFilter::createFilter(NullArrayFilterStub::class, null,  $parent->_getOptions());
		self::assertEquals('a', $filter->_getOptions()[NullArrayFilterStub::OPTION_CORRECT]);
		self::assertEquals('b', $filter->_getOptions()[NullArrayFilterStub::OPTION_SOFT_FAILURE]);

		try {
			$parent->setLogger($logger);
		}
		catch (\Throwable) {}
		self::expectException(\Exception::class);
		ArrayFilter::createFilter(NullArrayFilterStub::class, $logger, $parent->_getOptions());
	}


}
