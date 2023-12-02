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

use KampfCaspar\Filter\ValueFilter\IntegerValueFilter;
use PHPUnit\Framework\TestCase;

class IntegerValueFilterTest extends TestCase
{

	// built on top of double value filter -
	public function testFilterValue(): void
	{
		$filter = new IntegerValueFilter();
		self::assertEquals(42, $filter->filterValue(42));
		self::assertEquals(42, $filter->filterValue('42'));
		self::assertEquals(42, $filter->filterValue('0x2a'));
		self::assertEquals(42, $filter->filterValue('0b101010'));
		self::assertEquals(42, $filter->filterValue('052'));
		self::assertEquals(-42, $filter->filterValue(-42));
		self::assertEquals(-42, $filter->filterValue('-42'));
		self::assertEquals(-42, $filter->filterValue('-0x2a'));
		self::assertEquals(-42, $filter->filterValue('-0b101010'));
		self::assertEquals(-42, $filter->filterValue('-052'));
	}

	public function testRestrictedFormats(): void
	{
		$filter = new IntegerValueFilter([
			IntegerValueFilter::OPTION_FORMATS => 'dec',
			IntegerValueFilter::OPTION_SOFT_FAILURE => true,
		]);
		self::assertEquals(42, $filter->filterValue(42));
		self::assertEquals(42, $filter->filterValue('42'));
		self::assertNull($filter->filterValue('0x2a'));
		$filter->setOptions([
			IntegerValueFilter::OPTION_FORMATS => ['dec', 'bin'],
		]);
		self::assertEquals(42, $filter->filterValue('0b101010'));
		self::assertNull($filter->filterValue('052'));
	}
}
