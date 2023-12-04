<?php declare(strict_types=1);
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Test\Filter\ArrayFilter;

use KampfCaspar\Filter\ArrayFilter\CompoundedArrayFilter;
use KampfCaspar\Test\Filter\Stubs\NullArrayFilterStub;
use PHPUnit\Framework\TestCase;

class CompoundedArrayFilterTest extends TestCase
{

	public function testFilterArray(): void
	{
		$filter = new CompoundedArrayFilter([
			CompoundedArrayFilter::OPTION_COMPOUNDED_FILTERS => [
				NullArrayFilterStub::class,
				function (&$x) { unset($x['a']); return []; }
			]
		]);
		$arr = ['a' => 'a', 'b' => 'b'];
		$filter->filterArray($arr);
		self::assertArrayNotHasKey('a', $arr);
	}

}
