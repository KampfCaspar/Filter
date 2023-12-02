<?php
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Test\Filter\ValueFilter;

use KampfCaspar\Filter\ValueFilter\PregFilter;
use PHPUnit\Framework\TestCase;

class PregFilterTest extends TestCase
{

	public function testFilterValue(): void
	{
		$filter = new PregFilter([
			PregFilter::OPTION_PREGS => '/abc/',
			PregFilter::OPTION_SOFT_FAILURE => true,
		]);
		self::assertEquals('abcdef', $filter->filterValue('abcdef'));
		self::assertNull($filter->filterValue('bcdef'));
		$filter = new PregFilter([
			PregFilter::OPTION_PREGS => ['/abc/', '/^f/'],
			PregFilter::OPTION_SOFT_FAILURE => true,
		]);
		self::assertEquals('abcdef', $filter->filterValue('abcdef'));
		self::assertEquals('fg', $filter->filterValue('fg'));
		self::assertNull($filter->filterValue('bcdef'));
	}

	public function testFilterValueErrorNoRegex(): void
	{
		$filter = new PregFilter();
		self::expectException(\BadMethodCallException::class);
		$filter->filterValue(3.1);
	}

	public function testFilterValueErrorNoStringRegex(): void
	{
		$filter = new PregFilter([
			PregFilter::OPTION_PREGS => 3.1,
		]);
		self::expectException(\BadMethodCallException::class);
		$filter->filterValue(3.1);
	}

	public function testFilterValueErrorInvalidRegex(): void
	{
		$filter = new PregFilter([
			PregFilter::OPTION_PREGS => 'abc',
		]);
		self::expectException(\BadMethodCallException::class);
		$filter->filterValue(3.1);
	}
}
