<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\FrameworkPackage\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * OrmExtension is an extension for the Doctrine ORM library.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MigrationsExtension extends Kdyby\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition('migrations_manager')
			->setClass('Kdyby\Migrations\MigrationsManager', array(
				'@doctrine', '@application_packageManager'
			));

		$container->addDefinition('migrations_console_dialogHelper')
			->setClass('Kdyby\Migrations\Console\MigrationsManagerHelper', array(
				'@migrations_manager'
			))
			->addTag('console_helper', array('alias' => 'migrationsManager'));
	}

}