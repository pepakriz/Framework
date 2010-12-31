<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Data;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @property-read string $fullname
 * @MapperSuperClass
 */
abstract class Person extends Kdyby\Doctrine\IdentifiedEntity
{

	/** @Column(type="string", length=15) */
	private $salutation;

	/** @Column(type="string", length=50) */
	private $firstname;

	/** @Column(type="string", length=50) */
	private $secondname;

	/** @Column(type="string", length=50) */
	private $lastname;

	/**
     * @OneToOne(targetEntity="Kdyby\Location\Address")
     * @JoinColumn(name="address_id", referencedColumnName="id")
	 */
	private $address;


	public function getSalutation() { return $this->salutation; }
	public function setSalutation($salutation) { $this->salutation = $salutation; }

	public function getFirstname() { return $this->firstname; }
	public function setFirstname($firstname) { $this->firstname = $firstname; }

	public function getSecondname() { return $this->secondname; }
	public function setSecondname($secondname) { $this->secondname = $secondname; }

	public function getLastname() { return $this->lastname; }
	public function setLastname($lastname) { $this->lastname = $lastname; }

	public function getAddress() { return $this->address; }
	public function setAddress(Kdyby\Location\Address $address) { $this->address = $address; }

	public function getFullname()
	{
		return ($this->salutation ? $this->salutation .' ' : NULL) .
			($this->firstname ? $this->firstname . ' ' : NULL) .
			($this->secondname ? $this->secondname . ' ' : NULL) .
			$this->lastname;
	}

}