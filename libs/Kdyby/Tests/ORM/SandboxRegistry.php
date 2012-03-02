<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\ORM;

use Doctrine;
use Doctrine\Common\DataFixtures;
use Kdyby;
use Kdyby\Doctrine\Dao;
use Kdyby\Doctrine\Mapping\ClassMetadata;
use Kdyby\Tests\OrmTestCase;
use Nette;
use Symfony\Component\DependencyInjection\ContainerInterface;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SandboxRegistry extends Kdyby\Doctrine\Registry
{
	/**
	 * @var \Kdyby\Tests\ORM\DataFixturesLoader[]
	 */
	private $fixtureLoaders;

	/** @var \Kdyby\Doctrine\Dao[] */
	private $daoMocks = array();

	/** @var \Kdyby\Doctrine\Mapping\ClassMetadata[] */
	private $metaMocks = array();



	/**
	 * @param \Kdyby\Tests\OrmTestCase $testCase
	 */
	public function loadFixtures(OrmTestCase $testCase)
	{
		if ($this->fixtureLoaders === NULL) {
			$this->fixtureLoaders = array();

			foreach ($this->getEntityManagerNames() as $emName) {
				$this->fixtureLoaders[] = new DataFixturesLoader(
					$this->container->getService($emName . '.dataFixtures.loader'),
					$this->container->getService($emName . '.dataFixtures.executor')
				);
			}
		}

		foreach ($this->fixtureLoaders as $loader) {
			$loader->loadFixtures($testCase);
		}
	}



	/**
	 * Gets the EntityRepository for an entity.
	 *
	 * @param string $entityName        The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Doctrine\ORM\EntityRepository
	 */
	public function getRepository($entityName, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		if (isset($this->daoMocks[$entityManagerName][$lEntityName = strtolower($entityName)])) {
			return $this->daoMocks[$entityManagerName][$lEntityName];
		}

		return parent::getRepository($entityName, $entityManagerName);
	}



	/**
	 * @param string $entityName
	 * @param \Kdyby\Doctrine\Dao $dao
	 * @param string $entityManagerName
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Doctrine\Dao
	 */
	public function setRepository($entityName, Dao $dao, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		return $this->daoMocks[$entityManagerName][strtolower($entityName)] = $dao;
	}



	/**
	 * Gets the Dao for an entity.
	 *
	 * @param string $entityName        The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Doctrine\Dao
	 */
	public function getDao($entityName, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		if (isset($this->daoMocks[$entityManagerName][$lEntityName = strtolower($entityName)])) {
			return $this->daoMocks[$entityManagerName][$lEntityName];
		}

		return parent::getDao($entityName, $entityManagerName);
	}



	/**
	 * @param string $entityName
	 * @param \Kdyby\Doctrine\Dao $dao
	 * @param string $entityManagerName
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Doctrine\Dao
	 */
	public function setDao($entityName, Dao $dao, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		return $this->daoMocks[$entityManagerName][strtolower($entityName)] = $dao;
	}



	/**
	 * Gets the Dao for an entity.
	 *
	 * @param string $entityName        The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	public function getClassMetadata($entityName, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		if (isset($this->metaMocks[$entityManagerName][$lEntityName = strtolower($entityName)])) {
			return $this->metaMocks[$entityManagerName][$lEntityName];
		}

		return $this->getEntityManager($entityManagerName)->getClassMetadata($entityName);
	}



	/**
	 * @param string $entityName
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $meta
	 * @param string $entityManagerName
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	public function setClassMetadata($entityName, ClassMetadata $meta, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		return $this->metaMocks[$entityManagerName][strtolower($entityName)] = $meta;
	}

}
