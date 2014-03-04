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

namespace PSX\Controller;

use DateTime;
use PSX\Atom;
use PSX\Atom\Entry;
use PSX\Atom\Text;
use PSX\Data\Message;
use PSX\Data\Record\Mapper;
use PSX\Data\Record\Mapper\Rule;
use PSX\Data\RecordInterface;
use PSX\Data\WriterInterface;
use PSX\Util\Uuid;
use PSX\Filter\FilterDefinition;
use PSX\Handler\HandlerManipulationInterface;
use PSX\Handler\HandlerQueryInterface;

/**
 * This controller simplifies creating an API from an handler
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link	http://phpsx.org
 */
abstract class HandlerApiAbstract extends ApiAbstract
{
	protected $_handler;

	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doIndex()
	{
		try
		{
			if(!$this->getHandler() instanceof HandlerQueryInterface)
			{
				throw new Exception('Method not allowed', 405);
			}

			$params = $this->getRequestParams();
			$result = $this->getHandler()->getCollection($params['fields'], 
					$params['startIndex'], 
					$params['count'], 
					$params['sortBy'], 
					$params['sortOrder'], 
					$this->getRequestCondition());

			if($this->isWriter(WriterInterface::ATOM))
			{
				$this->setResponse($this->getAtomRecord($result));
			}
			else
			{
				$this->setResponse($result);
			}
		}
		catch(\Exception $e)
		{
			if($this->isWriter(WriterInterface::ATOM))
			{
				$msg = new Entry();
				$msg->setId(Uuid::nameBased($e->getMessage()));
				$msg->setTitle($e->getMessage());
				$msg->setUpdated(new DateTime());
			}
			else
			{
				$msg = new Message($e->getMessage(), false);
			}

			$this->setResponse($msg, null, $e->getCode());
		}
	}

	/**
	 * @httpMethod POST
	 * @path /
	 */
	public function doCreate()
	{
		try
		{
			if(!$this->getHandler() instanceof HandlerManipulationInterface)
			{
				throw new Exception('Method not allowed', 405);
			}

			$record = $this->import($this->getHandler()->getRecord());

			$this->beforeValidate($record);

			// validate
			$filterDefinition = $this->getFilterDefinition();

			if($filterDefinition instanceof FilterDefinition)
			{
				$filterDefinition->validate($record);
			}

			$this->afterValidate($record);

			// insert
			$this->beforeCreate($record);

			$this->getHandler()->create($record);

			$this->afterCreate($record);

			// message
			$msg = new Message('You have successful create a ' . $record->getRecordInfo()->getName(), true);

			$this->setResponse($msg);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg, null, $e->getCode());
		}
	}

	/**
	 * @httpMethod PUT
	 * @path /
	 */
	public function doUpdate()
	{
		try
		{
			if(!$this->getHandler() instanceof HandlerManipulationInterface)
			{
				throw new Exception('Method not allowed', 405);
			}

			$record = $this->getHandler()->getRecord();
			$record = $this->import($record);

			$this->beforeValidate($record);

			// validate
			$filterDefinition = $this->getFilterDefinition();

			if($filterDefinition instanceof FilterDefinition)
			{
				$filterDefinition->validate($record);
			}

			$this->afterValidate($record);

			// update
			$this->beforeUpdate($record);

			$this->getHandler()->update($record);

			$this->afterUpdate($record);

			// message
			$msg = new Message('You have successful update a ' . $record->getRecordInfo()->getName(), true);

			$this->setResponse($msg);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg, null, $e->getCode());
		}
	}

	/**
	 * @httpMethod DELETE
	 * @path /
	 */
	public function doDelete()
	{
		try
		{
			if(!$this->getHandler() instanceof HandlerManipulationInterface)
			{
				throw new Exception('Method not allowed', 405);
			}

			$record = $this->getHandler()->getRecord();
			$record = $this->import($record);

			$this->beforeValidate($record);

			// validate
			$filterDefinition = $this->getFilterDefinition();

			if($filterDefinition instanceof FilterDefinition)
			{
				$filterDefinition->validate($record);
			}

			$this->afterValidate($record);

			// delete
			$this->beforeDelete($record);

			$this->getHandler()->delete($record);

			$this->afterDelete($record);

			// message
			$msg = new Message('You have successful delete a ' . $record->getRecordInfo()->getName(), true);

			$this->setResponse($msg);
		}
		catch(\Exception $e)
		{
			$msg = new Message($e->getMessage(), false);

			$this->setResponse($msg, null, $e->getCode());
		}
	}

	protected function getHandler()
	{
		if($this->_handler === null)
		{
			$this->_handler = $this->getDefaultHandler();
		}

		return $this->_handler;
	}

	/**
	 * Method which is called before the record gets created
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	protected function beforeCreate(RecordInterface $record)
	{
	}

	/**
	 * Method which is called after the record was created
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	protected function afterCreate(RecordInterface $record)
	{
	}

	/**
	 * Method which is called before the record gets updated
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	protected function beforeUpdate(RecordInterface $record)
	{
	}

	/**
	 * Method which is called after the record was updated
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	protected function afterUpdate(RecordInterface $record)
	{
	}

	/**
	 * Method which is called before the record gets deleted
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	protected function beforeDelete(RecordInterface $record)
	{
	}

	/**
	 * Method which is called after the record was deleted
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	protected function afterDelete(RecordInterface $record)
	{
	}

	/**
	 * Method which is called before the record gets validated
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	protected function beforeValidate(RecordInterface $record)
	{
	}

	/**
	 * Method which is called after the record was validated
	 *
	 * @param PSX\Data\RecordInterface $record
	 */
	protected function afterValidate(RecordInterface $record)
	{
	}

	/**
	 * If we want display an atom feed we need to convert our record to an 
	 * Atom\Record. This method does the mapping. By default we return an entry
	 * that the ATOM format is not supported so you have to overwrite this 
	 * method
	 *
	 * @param PSX\Data\RecordInterface $result
	 * @return PSX\Atom
	 */
	protected function getAtomRecord(RecordInterface $result)
	{
		$msg = new Entry();
		$msg->setId(Uuid::nameBased($this->config['psx_url']));
		$msg->setTitle('ATOM format is not implemented');
		$msg->setUpdated(new DateTime());

		return $msg;
	}

	/**
	 * Returns the filter definition which validates values
	 *
	 * @return PSX\Filter\FilterDefinition
	 */
	protected function getFilterDefinition()
	{
		return null;
	}

	/**
	 * Returns the handler on which the API should operate
	 *
	 * @return PSX\Handler\HandlerInterface
	 */
	abstract protected function getDefaultHandler();
}