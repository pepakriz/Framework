<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Persistence;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IQueryObject
{

	/**
	 * @param IQueryable $repository
	 * @return integer
	 */
	function count(IQueryable $repository);


	/**
	 * @param IQueryable $repository
	 * @return mixed|\Kdyby\Doctrine\ResultSet
	 */
	function fetch(IQueryable $repository);


	/**
	 * @param IQueryable $repository
	 * @return object
	 */
	function fetchOne(IQueryable $repository);

}
