<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Domain\Users;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Kdyby\Tools\Mixed;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\Entity
 * @ORM\Table(name="users_info")
 */
class IdentityInfo extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @var \Kdyby\Security\Identity */
	private $identity;

	/** @ORM\Column(type="string", nullable=TRUE) */
	private $phone;

	/** @ORM\Column(type="array", nullable=TRUE) */
	private $data = array();



	/**
	 * @internal
	 * @param \Kdyby\Security\Identity $identity
	 * @return \Kdyby\Domain\Users\IdentityInfo
	 */
	final public function setIdentity(Kdyby\Security\Identity $identity)
	{
		if ($identity->getInfo() !== $this) {
			throw new Kdyby\InvalidArgumentException("Given identity does not own this info object.");
		}

		$this->identity = $identity;
		return $this;
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name};
		}

		return $this->data[$name];
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		if (!Mixed::isSerializable($value)) {
			throw new Kdyby\NotImplementedException;
		}

		if (isset($this->{$name})) {
			return $this->{$name} = $value;
		}

		$this->data[$name] = $value;
	}



	/**
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		if (isset($this->{$name})) {
			return TRUE;
		}

		return isset($this->data[$name]);
	}



	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name} = NULL;
		}

		unset($this->data[$name]);
	}

}
