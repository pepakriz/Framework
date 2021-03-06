<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Application\UI;

use Kdyby;
use Kdyby\Templates\TemplateConfigurator;
use Nette;
use Nette\Application\Responses;
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read \SystemContainer|\Nette\DI\Container $container
 * @property-read \Kdyby\Security\User $user
 * @property-read \Nette\Templating\FileTemplate $template
 * @property-read \Kdyby\Doctrine\Registry $doctrine
 *
 * @method \Kdyby\Security\User getUser() getUser()
 * @method \SystemContainer|\Nette\DI\Container getContext() getContext()
 */
abstract class Presenter extends Nette\Application\UI\Presenter
{

	/** @persistent */
	public $backlink;

	/** @var \Kdyby\Templates\TemplateConfigurator */
	protected $templateConfigurator;



	/**
	 * Add namespace into payload.
	 */
	protected function startup()
	{
		parent::startup();
		$this->payload->kdyby = (object)array();
	}



	/**
	 * @return \Kdyby\Doctrine\Registry
	 */
	public function getDoctrine()
	{
		return $this->getContext()->doctrine->registry;
	}



	/**
	 * @param string $entity
	 * @return \Kdyby\Doctrine\Dao
	 */
	public function getRepository($entity)
	{
		return $this->getDoctrine()->getDao($entity);
	}



	/**
	 * @param \Kdyby\Templates\TemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(TemplateConfigurator $configurator = NULL)
	{
		$this->templateConfigurator = $configurator;
	}



	/**
	 * @param string|null $class
	 *
	 * @return \Nette\Templating\Template
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->configure($template);
		}

		return $template;
	}



	/**
	 * @param \Nette\Templating\Template $template
	 *
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		$engine = $this->getPresenter()->getContext()->nette->createLatte();
		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->prepareFilters($engine);
		}

		$template->registerFilter($engine);
	}



	/**
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		if (!$this->isInPackage()) {
			return parent::formatLayoutTemplateFiles();
		}

		$presenter = substr($name = $this->getName(), strrpos(':' . $name, ':'));
		$layout = $this->layout ? $this->layout : 'layout';

		$mapper = function ($views) use ($presenter, $layout) {
			return array(
				"$views/$presenter/@$layout.latte",
				"$views/$presenter.@$layout.latte",
				"$views/@$layout.latte",
			);
		};

		return array_merge(
			$mapper(realpath(dirname($this->getReflection()->getFileName()) . '/../Resources/view')),
			$mapper($this->context->expand('%appDir%/templates'))
		);
	}



	/**
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		if (!$this->isInPackage()) {
			return parent::formatTemplateFiles();
		}

		$presenter = substr($name = $this->getName(), strrpos(':' . $name, ':'));
		$view = $this->view;

		$mapper = function ($views) use ($presenter, $view) {
			return array(
				"$views/$presenter/$view.latte",
				"$views/$presenter.$view.latte",
			);
		};

		return array_merge(
			$mapper(realpath(dirname($this->getReflection()->getFileName()) . '/../Resources/view')),
			$mapper($this->context->expand('%appDir%/templates'))
		);
	}



	/**
	 * Presenter is in package, when "Package" keyword is in it's namespace
	 * and "Module" keyword is not. Because packages disallow modules.
	 *
	 * @return bool
	 */
	private function isInPackage()
	{
		return stripos(get_called_class(), 'Package\\') !== FALSE
			&& stripos(get_called_class(), 'Module\\') === FALSE;
	}



	/**
	 * Sends AJAX payload to the output.
	 *
	 * @param array|object|null $payload
	 *
	 * @return void
	 * @throws \Nette\Application\AbortException
	 */
	public function sendPayload($payload = NULL)
	{
		if ($payload !== NULL) {
			$this->sendResponse(new Responses\JsonResponse($payload));
		}

		parent::sendPayload();
	}



	/**
	 * @param string $name
	 * @return \Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$method = 'createComponent' . ucfirst($name);
		if (method_exists($this, $method)) {
			$this->checkRequirements($this->getReflection()->getMethod($method));
		}

		return parent::createComponent($name);
	}



	/**
	 * Checks for requirements such as authorization.
	 *
	 * @param \Reflector $element
	 *
	 * @return void
	 */
	public function checkRequirements($element)
	{
		if ($element instanceof \Reflector) {
			$this->getUser()->protectElement($element);
		}
	}

}
