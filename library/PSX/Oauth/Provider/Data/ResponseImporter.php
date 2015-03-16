<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2015 Christoph Kappestein <k42b3.x@gmail.com>
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PSX\Oauth\Provider\Data;

use InvalidArgumentException;
use PSX\Data\InvalidDataException;
use PSX\Data\RecordInterface;
use PSX\Data\Record\ImporterInterface;
use PSX\Http\Message;

/**
 * ResponseImporter
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ResponseImporter
{
	public function import(RecordInterface $record, $data)
	{
		if(!is_array($data) && !$data instanceof \stdClass)
		{
			throw new InvalidArgumentException('Received invalid data');
		}

		foreach($data as $key => $value)
		{
			switch($key)
			{
				case 'oauth_token':
					$record->setToken($value);
					break;

				case 'oauth_token_secret':
					$record->setTokenSecret($value);
					break;

				default:
					$record->addParam($key, $value);
					break;
			}
		}
	}
}
