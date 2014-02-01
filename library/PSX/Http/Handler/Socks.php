<?php
/*
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace PSX\Http\Handler;

use PSX\Http;
use PSX\Http\HandlerException;
use PSX\Http\HandlerInterface;
use PSX\Http\NotSupportedException;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\ResponseParser;
use PSX\Http\Stream\SocksStream;

/**
 * Socks
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Socks implements HandlerInterface
{
	public function request(Request $request, $count = 0)
	{
		// ssl
		$scheme = null;

		if(!$request->isSSL())
		{
			$scheme = 'tcp';
		}
		else
		{
			$transports = stream_get_transports();

			if(in_array('tls', $transports))
			{
				$scheme = 'tls';
			}
			else if(in_array('ssl', $transports))
			{
				$scheme = 'ssl';
			}
			else
			{
				throw new NotSupportedException('https is not supported');
			}
		}

		// port
		$port = $request->getUrl()->getPort();

		if(empty($port))
		{
			$port = getservbyname($request->getUrl()->getScheme(), 'tcp');
		}

		// open socket
		$handle = @fsockopen($scheme . '://' . $request->getUrl()->getHost(), $port, $errno, $errstr);

		if($handle !== false)
		{
			// timeout
			$timeout = $request->getTimeout();

			if(!empty($timeout))
			{
				stream_set_timeout($handle, $timeout);
			}

			// callback
			$callback = $request->getCallback();

			if(!empty($callback))
			{
				call_user_func_array($callback, array($handle, $request));
			}

			// write header
			$headers = $request->getHeaders();

			fwrite($handle, $request->getLine() . Http::$newLine);

			foreach($headers as $key => $value)
			{
				fwrite($handle, $key . ': ' . $value . Http::$newLine);
			}

			fwrite($handle, Http::$newLine);

			// write body
			$body = $request->getBody();

			if(is_resource($body))
			{
				stream_copy_to_stream($body, $handle);
			}
			else if(!empty($body))
			{
				fwrite($handle, (string) $body);
			}

			fflush($handle);

			// read header
			$headers = array();

			do
			{
				$header = trim(fgets($handle));

				if(!empty($header))
				{
					$headers[] = $header;
				}
			}
			while(!empty($header));

			// build response
			$response = ResponseParser::buildResponseFromHeader($headers);

			// create stream
			$contentLength   = (integer) (string) $response->getHeader('Content-Length');
			$chunkedEncoding = $response->getHeader('Transfer-Encoding') == 'chunked';

			if($request->getMethod() != 'HEAD')
			{
				$response->setBody(new SocksStream($handle, $contentLength, $chunkedEncoding));
			}
			else
			{
				fclose($handle);

				$response->setBody(null);
			}

			return $response;
		}
		else
		{
			throw new HandlerException(!empty($errstr) ? $errstr : 'Could not open socket');
		}
	}
}

