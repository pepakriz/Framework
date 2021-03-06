<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Security\RBAC;

use Doctrine;
use Kdyby;
use Kdyby\Persistence\IQueryable;
use Kdyby\Security\Identity;
use Nette;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class UserPermissionsQuery extends Kdyby\Doctrine\QueryObjectBase
{

	/** @var Identity */
	private $identity;

	/** @var Division */
	private $division;



	/**
	 * @param Identity $identity
	 * @param Division $division
	 * @param Paginator $paginator
	 */
	public function __construct(Identity $identity, Division $division, Paginator $paginator = NULL)
	{
		parent::__construct($paginator);
		$this->identity = $identity;
		$this->division = $division;
	}



	/**
	 * @param IQueryable $repository
	 * @return Doctrine\ORM\QueryBuilder
	 */
	protected function doCreateQuery(IQueryable $repository)
	{
		return $repository->createQueryBuilder('perm')->select('perm', 'priv', 'act', 'res')
			->innerJoin('perm.privilege', 'priv')
			->innerJoin('perm.division', 'div')
			->innerJoin('perm.identity', 'ident')
			->innerJoin('priv.action', 'act')
			->innerJoin('priv.resource', 'res')
			->where('ident = :identity')
				->setParameter('identity', $this->identity)
			->andWhere('div = :division')
				->setParameter('division', $this->division);
	}

}
