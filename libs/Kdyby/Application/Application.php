<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Application extends Nette\Application\Application
{

	/** @var \Kdyby\Config\Configurator */
	private $configurator;

	/** @var \Kdyby\Application\RequestManager */
	private $requestsManager;

	/** @var \Kdyby\Packages\PackageManager */
	private $packageManager;



	/**
	 * @param array|string|\Nette\Config\Configurator $params
	 * @param string $environment
	 * @param string $productionMode
	 */
	public function __construct($params = NULL, $environment = NULL, $productionMode = NULL)
	{
		if ($params instanceof Kdyby\Config\Configurator) {
			$this->configurator = $params;

		} else {
			$this->configurator = $this->createConfigurator($params);
		}

		// environment
		if ($environment !== NULL) {
			$this->configurator->setEnvironment($environment);
		}

		// production mode
		if ($productionMode !== NULL) {
			$this->configurator->setProductionMode($productionMode);
		}

		// inject application instance
		$container = $this->configurator->getContainer();
		$container->addService('application', $this);

		// dependencies
		$this->packageManager = $container->application_packageManager;
		$this->requestsManager = $container->application_storedRequestsManager;
		parent::__construct(
			$container->presenterFactory,
			$container->router,
			$container->httpRequest,
			$container->httpResponse,
			$container->session
		);

		// wire events
		$packages = $this->configurator->getPackages();
		$packages->setContainer($container);
		$packages->attach($this);

		// activate packages
		$this->packageManager->setActive($packages);

		// call debug
		if (Debugger::$productionMode === FALSE) {
			$packages->debug();
		}
	}



	/**
	 * @param array $params
	 *
	 * @return \Kdyby\Config\Configurator
	 */
	protected function createConfigurator($params)
	{
		return new Kdyby\Config\Configurator($params);
	}



	/**
	 * @return \Kdyby\Config\Configurator
	 */
	public function getConfigurator()
	{
		return $this->configurator;
	}



	/********************* Request serialization *********************/



	/**
	 * Stores current request to session.
	 *
	 * @param string $expiration
	 *
	 * @return string
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		return $this->requestsManager->storeCurrentRequest($expiration);
	}



	/**
	 * Restores current request to session.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$this->requestsManager->restoreRequest($key);
	}



	/********************* Packages *********************/



	/**
	 * Checks if a given class name belongs to an active package.
	 *
	 * @param string $class
	 *
	 * @return boolean
	 */
	public function isClassInActivePackage($class)
	{
		return $this->packageManager->isClassInActivePackage($class);
	}



	/**
	 * @see \Kdyby\Package\PackageManager::locateResource()
	 *
	 * @param string $name  A resource name to locate
	 *
	 * @return string|array
	 */
	public function locateResource($name)
	{
		return $this->packageManager->locateResource($name);
	}

}
