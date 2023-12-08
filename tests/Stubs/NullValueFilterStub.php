<?php declare(strict_types=1);
/**
 * This program is free software: you can redistribute it and/or modify it under the terms of the
 * GNU Affero General Public License as published by the Free Software Foundation, either version 3
 * of the License, or (at your option) any later version.
 *
 * @license AGPL-3.0-or-later
 * @author KampfCaspar <code@kampfcaspar.ch>
 */

namespace KampfCaspar\Test\Filter\Stubs;

use KampfCaspar\Filter\ValueFilter;
use Psr\Log\LoggerInterface;

class NullValueFilterStub extends ValueFilter
{
	public function setLogger(LoggerInterface $logger): void
	{
		parent::setLogger($logger);
		throw new \Exception('success');
	}

	protected function filterIndividualValue(mixed $value): mixed
	{
		return $value;
	}
}