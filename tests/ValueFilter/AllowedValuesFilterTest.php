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

use KampfCaspar\Filter\Exception\FilteringException;
use KampfCaspar\Filter\ValueFilter\AllowedValuesFilter;
use PHPUnit\Framework\TestCase;

class AllowedValuesFilterTest extends TestCase
{

	public function testFilter(): void
	{
		$filter = new AllowedValuesFilter();
		$filter->setOptions([
			AllowedValuesFilter::OPTION_SCALARITY => null,
			AllowedValuesFilter::OPTION_SOFT_FAILURE => true,
			AllowedValuesFilter::OPTION_WHITELIST => ['alpha', 'beta'],
		]);
		self::assertEquals('alpha', $filter->filterValue('alpha'));
		self::assertNull($filter->filterValue('gamma'));
		self::assertEquals(['alpha', 'beta'], $filter->filterValue(['alpha', 'beta']));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_DEFAULT => 'delta',
		]);
		self::assertEquals('delta', $filter->filterValue('gamma'));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_DEFAULT => ['delta', 'epsilon'],
		]);
		self::assertEquals(['delta', 'epsilon'], $filter->filterValue('gamma'));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_SOFT_FAILURE => false,
		]);
		self::expectException(FilteringException::class);
		$filter->filterValue('gamma');
	}

	public function testFilterScalar(): void
	{
		$filter = new AllowedValuesFilter();
		$filter->setOptions([
			//AllowedValuesFilter::OPTION_SCALARITY => 'scalar',
			AllowedValuesFilter::OPTION_SOFT_FAILURE => true,
			AllowedValuesFilter::OPTION_WHITELIST => ['alpha', 'beta'],
		]);
		self::assertEquals('alpha', $filter->filterValue('alpha'));
		self::assertNull($filter->filterValue(['alpha']));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_DEFAULT => 'delta',
		]);
		self::assertEquals('delta', $filter->filterValue('gamma'));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_DEFAULT => ['delta', 'epsilon'],
		]);
		self::assertEquals('delta', $filter->filterValue('gamma'));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_SOFT_FAILURE => false,
		]);
		self::expectException(FilteringException::class);
		$filter->filterValue(['alpha']);
	}

	public function testFilterArray(): void
	{
		$filter = new AllowedValuesFilter();
		$filter->setOptions([
			AllowedValuesFilter::OPTION_SCALARITY => 'list',
			AllowedValuesFilter::OPTION_SOFT_FAILURE => true,
			AllowedValuesFilter::OPTION_WHITELIST => ['alpha', 'beta'],
		]);
		self::assertEquals(['alpha'], $filter->filterValue('alpha'));
		self::assertEquals(['alpha'], $filter->filterValue(['alpha']));
		self::assertEquals(['alpha'], $filter->filterValue(['alpha', 'gamma']));
		self::assertEquals([], $filter->filterValue(['gamma']));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_DEFAULT => 'delta',
		]);
		self::assertEquals(['delta'], $filter->filterValue('gamma'));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_DEFAULT => ['delta', 'epsilon'],
		]);
		self::assertEquals(['delta', 'epsilon'], $filter->filterValue('gamma'));
		$filter->setOptions([
			AllowedValuesFilter::OPTION_SOFT_FAILURE => false,
		]);
		self::expectException(FilteringException::class);
		$filter->filterValue(['alpha', 'gamma']);
	}
}
