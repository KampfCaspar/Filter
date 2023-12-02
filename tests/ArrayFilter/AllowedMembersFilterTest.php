<?php
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Test\Filter\ArrayFilter;

use Beste\Psr\Log\TestLogger;
use KampfCaspar\Filter\ArrayFilter\AllowedMembersFilter;
use PHPUnit\Framework\TestCase;

class AllowedMembersFilterTest extends TestCase
{

	private function referenceDetour(AllowedMembersFilter $filter, $arr): array
	{
		return $filter->filterArray($arr);
	}

	public function testFilterArray(): void
	{
		$filter = new AllowedMembersFilter([
			AllowedMembersFilter::OPTION_MANDATORY => ['a'],
			AllowedMembersFilter::OPTION_OPTIONAL => 'b',
			AllowedMembersFilter::OPTION_SOFT_FAILURE => true,
		]);

		$logger = TestLogger::create();
		$filter->setLogger($logger);
		$arr = ['a' => 'a'];
		self::assertCount(0, $this->referenceDetour($filter, $arr));
		self::assertCount(0, $this->referenceDetour($filter, new \ArrayObject($arr)));
		self::assertCount(0, $this->referenceDetour($filter, new \ArrayIterator($arr)));
		self::assertCount(0, $logger->records);

		$arr = ['a' => 'a', 'b' => 'b'];
		self::assertCount(0, $this->referenceDetour($filter, $arr));
		self::assertCount(0, $this->referenceDetour($filter, new \ArrayObject($arr)));
		self::assertCount(0, $this->referenceDetour($filter, new \ArrayIterator($arr)));
		self::assertCount(0, $logger->records);

		$arr = ['b' => 'b'];
		self::assertCount(1, $this->referenceDetour($filter, $arr));
		self::assertCount(1, $logger->records);

		$arr = ['a' => 'a', 'c' => 'c'];
		self::assertCount(1, $this->referenceDetour($filter, new \ArrayObject($arr)));
		self::assertCount(2, $logger->records);

		$filter->setOptions([
			AllowedMembersFilter::OPTION_SOFT_FAILURE => false,
		]);
		self::expectException(\DomainException::class);
		$filter->filterArray($arr);
	}

	public function testFilterArrayClear(): void
	{
		$filter = new AllowedMembersFilter([
			AllowedMembersFilter::OPTION_MANDATORY => ['a'],
			AllowedMembersFilter::OPTION_OPTIONAL => 'b',
			AllowedMembersFilter::OPTION_SOFT_FAILURE => false,
			AllowedMembersFilter::OPTION_CORRECT => true,
		]);

		$logger = TestLogger::create();
		$filter->setLogger($logger);
		$obj = new \ArrayIterator(['a' => 'a', 'c' => 'c']);
		self::assertCount(1, $filter->filterArray($obj));
		self::assertCount(1, $logger->records);
		self::assertArrayNotHasKey('c', $obj);
	}

}
