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

namespace PSX\Handler\Manager;

use Doctrine\DBAL\Connection;
use Psr\Cache\CacheItemPoolInterface;
use PSX\Handler\HandlerManagerInterface;
use PSX\Sql\Table\Definition;
use PSX\Sql\Table\ReaderInterface;

/**
 * TableManager
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class TableManager implements HandlerManagerInterface
{
	const NAME = 'table';

	/**
	 * @var Doctrine\DBAL\Connection
	 */
	protected $connection;

	/**
	 * @var PSX\Sql\Table\ReaderInterface
	 */
	protected $defaultReader;

	/**
	 * @var Psr\Cache\CacheItemPoolInterface
	 */
	protected $cache;

	/**
	 * @var integer
	 */
	protected $expire;

	protected $_container;

	public function __construct(Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * Sets the default reader wich is used if no explicit reader was specified
	 *
	 * @param PSX\Sql\Table\ReaderInterface $defaultReader
	 */
	public function setDefaultReader(ReaderInterface $defaultReader)
	{
		$this->defaultReader = $defaultReader;
	}

	/**
	 * If set the table definition results are cached with the specific handler
	 * Note the cache doesnt expire so you have to delete the cache manually
	 * if the definition has changed
	 *
	 * @param Psr\Cache\CacheItemPoolInterface $cache
	 * @param integer $expire
	 */
	public function setCache(CacheItemPoolInterface $cache, $expire = null)
	{
		$this->cache  = $cache;
		$this->expire = $expire;
	}

	public function getName()
	{
		return self::NAME;
	}

	public function get($tableName)
	{
		if(isset($this->_container[$tableName]))
		{
			return $this->_container[$tableName];
		}

		// if a cache handler is set try to read the definition from the cache
		// but only if we haven an reader
		if($this->cache !== null && $this->defaultReader !== null)
		{
			$key  = '__TD__' . md5(__METHOD__ . '-' . $tableName);
			$item = $this->cache->getItem($key);

			if($item->isHit())
			{
				$definition = $item->get();

				$this->_container[$tableName] = new Table($this->connection,
					$definition->getName(),
					$definition->getColumns(), 
					$definition->getConnections());
			}
		}

		if(!isset($this->_container[$tableName]))
		{
			if($this->defaultReader === null)
			{
				// we assume that $tableName is an class name of an 
				// TableInterface implementation
				$this->_container[$tableName] = new $tableName($this->connection);
			}
			else
			{
				$definition = $this->defaultReader->getTableDefinition($tableName);

				$this->_container[$tableName] = new Table($this->connection,
					$definition->getName(),
					$definition->getColumns(), 
					$definition->getConnections());

				// if a cache item is set write definition to the cache
				if(isset($item))
				{
					$item->set($definition, $this->expire);

					$this->cache->save($item);
				}
			}
		}

		return $this->_container[$tableName];
	}
}