<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\EventDispatcher;

use Kdyby;
use Kdyby\EventDispatcher\EventArgs;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EventArgsTest extends Kdyby\Tests\TestCase
{

	public function testImplementsDoctrineEventArgs()
	{
		$args = new EventArgsMock();
		$this->assertInstanceOf('Doctrine\Common\EventArgs', $args);
	}

}
