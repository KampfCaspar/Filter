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

use KampfCaspar\Filter\Exception\OptionsException;
use KampfCaspar\Filter\ValueFilter\PregSplitFilter;
use PHPUnit\Framework\TestCase;

class PregSplitFilterTest extends TestCase
{

	public function testFilterValue(): void
	{
		$filter = new PregSplitFilter([
			PregSplitFilter::OPTION_SCALARITY => null,
		]);
		self::assertEquals(['alpha'], $filter->filterValue('alpha'));
		self::assertEquals(['alpha', 'beta'], $filter->filterValue('alpha  beta'));
		self::assertEquals(['alpha', 'beta'], $filter->filterValue(['alpha', 'beta']));
		$filter->setOptions([
			PregSplitFilter::OPTION_SPLIT => '/\\s*,\\s*/',
		]);
		self::assertEquals(['alpha', 'beta'], $filter->filterValue('alpha, beta'));
		$filter->setOptions([
			PregSplitFilter::OPTION_LIMIT => 2,
		]);
		self::assertEquals(['alpha', 'beta, gamma'], $filter->filterValue('alpha, beta, gamma'));
	}

	public function testFilterValueErrorNoStringRegex(): void
	{
		$filter = new PregSplitFilter([
			PregSplitFilter::OPTION_SPLIT => 3.1,
		]);
		self::expectException(OptionsException::class);
		$filter->filterValue('alpha');
	}

	public function testFilterValueErrorInvalidRegex(): void
	{
		$filter = new PregSplitFilter([
			PregSplitFilter::OPTION_SPLIT => 'abc',
		]);
		self::expectException(OptionsException::class);
		$filter->filterValue('alpha');
	}

}
