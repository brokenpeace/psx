<?php
/*
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2015 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of psx. psx is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * psx is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with psx. If not, see <http://www.gnu.org/licenses/>.
 */

namespace PSX\Data\Schema\Property;

/**
 * DateTimeTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class DateTimeTest extends \PHPUnit_Framework_TestCase
{
	public function testValidate()
	{
		$property = new DateTime('test');

		$this->assertTrue($property->validate('2002-10-10T17:00:00'));
		$this->assertTrue($property->validate('2002-10-10T17:00:00Z'));
		$this->assertTrue($property->validate('2002-10-10T17:00:00+13:00'));
	}

	/**
	 * @expectedException PSX\Data\Schema\ValidationException
	 */
	public function testValidateInvalidFormat()
	{
		$property = new DateTime('test');

		$this->assertTrue($property->validate('foo'));
	}

	/**
	 * @expectedException PSX\Data\Schema\ValidationException
	 */
	public function testValidateInvalidTimezone()
	{
		$property = new DateTime('test');

		$this->assertTrue($property->validate('2002-10-10T17:00:00+25:00'));
	}

	public function testGetId()
	{
		$property = new DateTime('test');

		$this->assertEquals('8319e693891940b6a51823148dc49af1', $property->getId());
	}
}
