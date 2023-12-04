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

use KampfCaspar\Filter\ValueFilter;
use KampfCaspar\Filter\ValueFilter\CompoundedValueFilter;
use KampfCaspar\Test\Filter\Stubs\NullValueFilterStub;
use PHPUnit\Framework\TestCase;

class CompoundedValueFilterTest extends TestCase
{

	public function testFilterValue(): void
	{
		$filter = new CompoundedValueFilter([
			CompoundedValueFilter::OPTION_COMPOUNDED_FILTERS => [
				NullValueFilterStub::class,
				[ValueFilter::OPTION_FILTER => NullValueFilterStub::class],
				fn ($x) => 2*$x,
			]
		]);
		$this->assertEquals(6, $filter->filterValue(3));
	}
}
