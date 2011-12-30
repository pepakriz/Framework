<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage\Presenter;

use Kdyby;
use Kdyby\Package\AsseticPackage\Response\AssetResponse;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticPresenter extends Nette\Object implements Nette\Application\IPresenter
{

	/** @var \Kdyby\Assets\IStorage */
	private $writer;



	/**
	 * @param \Kdyby\Assets\IStorage $writer
	 */
	public function __construct(Kdyby\Assets\IStorage $writer)
	{
		$this->writer = $writer;
	}



	/**
	 * @param \Nette\Application\Request $request
	 *
	 * @return \Nette\Application\IResponse
	 */
	public function run(Nette\Application\Request $request)
	{
		$outputAsset = trim(@$request->parameters['name'], '/');
		return new AssetResponse($this->writer->getAssetRealpath($outputAsset));
	}

}