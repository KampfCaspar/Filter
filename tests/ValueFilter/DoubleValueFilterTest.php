<?php declare(strict_types=1);
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Test\Filter\ValueFilter;

use KampfCaspar\Filter\ValueFilter\DoubleValueFilter;
use PHPUnit\Framework\TestCase;

class DoubleValueFilterTest extends TestCase
{

	public function testFilterValue(): void
	{
		$filter = new DoubleValueFilter();
		$filter->setOptions([
			DoubleValueFilter::OPTION_SCALARITY => null,
			DoubleValueFilter::OPTION_SOFT_FAILURE => true,
		]);
		self::assertEquals(3.1, $filter->filterValue(3.1));
		self::assertNull($filter->filterValue('gamma'));
		self::assertEquals([3.14, 2.71], $filter->filterValue([3.14, 2.71]));
		$filter->setOptions([
			DoubleValueFilter::OPTION_DEFAULT => 2.71,
			DoubleValueFilter::OPTION_MIN => 3.0,
			DoubleValueFilter::OPTION_MAX => 4.0,
		]);
		self::assertEquals(2.71, $filter->filterValue('gamma'));
		self::assertEquals(3.0, $filter->filterValue(2.71));
		self::assertEquals(4.0, $filter->filterValue(6.28));
		self::assertEquals([3.0, 4.0], $filter->filterValue([2.71, 6.28]));
		$filter->setOptions([
			DoubleValueFilter::OPTION_DEFAULT => [3.14, 2.71],
		]);
		self::assertEquals([3.14, 2.71], $filter->filterValue('gamma'));
		$filter->setOptions([
			DoubleValueFilter::OPTION_SOFT_FAILURE => false,
		]);
		self::expectException(\InvalidArgumentException::class);
		$filter->filterValue('gamma');
	}

	public function testFilterScalar(): void
	{
		$filter = new DoubleValueFilter();
		$filter->setOptions([
			//DoubleValueFilter::OPTION_SCALARITY => null,
			DoubleValueFilter::OPTION_SOFT_FAILURE => true,
		]);
		self::assertEquals(3.1, $filter->filterValue(3.1));
		self::assertNull($filter->filterValue('gamma'));
		self::assertNull($filter->filterValue([3.14, 2.71]));
		$filter->setOptions([
			DoubleValueFilter::OPTION_DEFAULT => 2.71,
			DoubleValueFilter::OPTION_MIN => 3.0,
			DoubleValueFilter::OPTION_MAX => 4.0,
		]);
		self::assertEquals(2.71, $filter->filterValue('gamma'));
		self::assertEquals(3.0, $filter->filterValue(2.71));
		self::assertEquals(4.0, $filter->filterValue(6.28));
		self::assertEquals(2.71, $filter->filterValue([2.71, 6.28]));
		$filter->setOptions([
			DoubleValueFilter::OPTION_DEFAULT => [3.14, 2.71],
		]);
		self::assertEquals(3.14, $filter->filterValue('gamma'));
		$filter->setOptions([
			DoubleValueFilter::OPTION_SOFT_FAILURE => false,
		]);
		self::expectException(\InvalidArgumentException::class);
		$filter->filterValue('gamma');
	}

	public function testFilterArray(): void
	{
		$filter = new DoubleValueFilter();
		$filter->setOptions([
			DoubleValueFilter::OPTION_SCALARITY => 'array',
			DoubleValueFilter::OPTION_SOFT_FAILURE => true,
		]);
		self::assertEquals([3.1], $filter->filterValue(3.1));
		self::assertEquals([], $filter->filterValue('gamma'));
		self::assertEquals([3.14, 2.71], $filter->filterValue([3.14, 2.71]));
		$filter->setOptions([
			DoubleValueFilter::OPTION_DEFAULT => 2.71,
			DoubleValueFilter::OPTION_MIN => 3.0,
			DoubleValueFilter::OPTION_MAX => 4.0,
		]);
		self::assertEquals([2.71], $filter->filterValue('gamma'));
		self::assertEquals([3.0], $filter->filterValue(2.71));
		self::assertEquals([4.0], $filter->filterValue(6.28));
		self::assertEquals([3.0, 4.0], $filter->filterValue([2.71, 6.28]));
		$filter->setOptions([
			DoubleValueFilter::OPTION_DEFAULT => [3.14, 2.71],
		]);
		self::assertEquals([3.14, 2.71], $filter->filterValue('gamma'));
		$filter->setOptions([
			DoubleValueFilter::OPTION_SOFT_FAILURE => false,
		]);
		self::expectException(\InvalidArgumentException::class);
		$filter->filterValue('gamma');

	}
}
