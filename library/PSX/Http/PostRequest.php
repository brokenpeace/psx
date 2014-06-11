<?php
/*
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2014 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace PSX\Http;

use Psr\HttpMessage\StreamInterface;
use PSX\Url;

/**
 * PostRequest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class PostRequest extends Request
{
	/**
	 * __construct
	 *
	 * @param PSX\Url|string $url
	 * @param array $header
	 * @param Psr\HttpMessage\StreamInterface|string|array $body
	 */
	public function __construct($url, array $header = array(), $body = null)
	{
		$url    = $url instanceof Url ? $url : new Url((string) $url);
		$method = 'POST';

		$isFormUrlencoded = false;

		if(is_array($body))
		{
			$isFormUrlencoded = true;

			$body = http_build_query($body, '', '&');
		}

		parent::__construct($url, $method, $header, $body);

		if(!$this->hasHeader('Host'))
		{
			$this->setHeader('Host', $url->getHost());
		}

		if($isFormUrlencoded)
		{
			$this->setHeader('Content-Type', 'application/x-www-form-urlencoded');
		}
	}

	public function setBody(StreamInterface $body = null)
	{
		parent::setBody($body);

		if($body !== null)
		{
			$this->setHeader('Content-Length', $body->getSize());
		}
	}
}

