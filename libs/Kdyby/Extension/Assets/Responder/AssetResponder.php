<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\Responder;

use Kdyby;
use Kdyby\Extension\Assets;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetResponder extends Nette\Object
{

	/** @var \Kdyby\Extension\Assets\Storage\CacheStorage */
	private $storage;



	/**
	 * @param \Kdyby\Extension\Assets\Storage\CacheStorage $storage
	 */
	public function __construct(Assets\Storage\CacheStorage $storage)
	{
		$this->storage = $storage;
	}



	/**
	 * @param string $prefix
	 * @param string $name
	 *
	 * @return \Kdyby\Extension\Assets\Response\AssetResponse
	 */
	public function __invoke($prefix, $name)
	{
		return new Assets\Response\AssetResponse($this->storage, trim($prefix, '/') . '/' . trim($name, '/'));
	}

}
