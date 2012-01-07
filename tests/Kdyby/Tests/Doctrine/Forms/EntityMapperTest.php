<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Forms;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Kdyby\Doctrine\Forms\CollectionContainer;
use Kdyby\Doctrine\Forms\EntityMapper;
use Kdyby\Doctrine\Forms\EntityContainer;
use Kdyby\Doctrine\Forms\Form;
use Nette;
use Nette\Forms\Controls;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EntityMapperTest extends Kdyby\Tests\OrmTestCase
{

	/** @var \Kdyby\Doctrine\Forms\EntityMapper */
	private $mapper;



	public function setUp()
	{
		$this->createOrmSandbox(array(
			__NAMESPACE__ . '\Fixtures\RootEntity',
			__NAMESPACE__ . '\Fixtures\RelatedEntity',
		));

		$this->mapper = new EntityMapper($this->getDoctrine());
	}



	public function testAssigningEntity()
	{
		$entity = new Fixtures\RootEntity("Chuck Norris");
		$component = new Nette\Forms\Container;

		$this->mapper->assign($entity, $component);
		$this->assertSame($entity, current($this->mapper->getEntities()));
		$this->assertSame($component, $this->mapper->getComponent($entity));
	}



	public function testSettingControlAlias()
	{
		$name = new Controls\TextInput('Name');
		$name->setParent(NULL, 'name');

		$surname = new Controls\TextInput('Surname');
		$surname->setParent(NULL, 'surname');

		$this->mapper->setControlAlias($name, 'title');

		$this->assertEquals('title', $this->mapper->getControlField($name));
		$this->assertEquals('surname', $this->mapper->getControlField($surname));
	}



	/**
	 * @return array
	 */
	public function dataItemControls()
	{
		return array(
			array(new Controls\SelectBox),
			array(new Kdyby\Forms\Controls\CheckboxList),
			array(new Controls\RadioList),
		);
	}



	/**
	 * @dataProvider dataItemControls
	 *
	 * @param \Nette\Forms\IControl $itemsControl
	 */
	public function testItemsControlLoading_RelatedPairsQuery(Nette\Forms\IControl $itemsControl)
	{
		// tested control dependencies
		$entity = new Fixtures\RootEntity("Chuck Norris");
		$container = new EntityContainer($entity);
		$container['children'] = $itemsControl;
		$this->mapper->assign($entity, $container);

		// control mapper will require DAO
		$relatedDao = $this->getMockBuilder('Kdyby\Doctrine\Dao')
			->disableOriginalConstructor()
			->getMock();
		$this->getDoctrine()->setDao(__NAMESPACE__ . '\Fixtures\RelatedEntity', $relatedDao);

		// by default, there is no mapper
		$this->assertNull($this->mapper->getControlMapper($itemsControl));
		$this->mapper->setControlMapper($itemsControl, 'name', 'id');

		// mapper should be closure
		$mapper = $this->mapper->getControlMapper($itemsControl);
		$this->assertInstanceOf('Closure', $mapper);

		// map to control
		$relatedDao->expects($this->atLeastOnce())
			->method('fetchPairs')
			->with($this->isInstanceOf('Kdyby\Doctrine\Forms\ItemPairsQuery'))
			->will($this->returnValue($items = array(1 => 'Lorem')));

		$this->mapper->loadControlItems();
		$this->assertEquals($items, $itemsControl->getItems());
	}



	public function testItemsControlLoading_FromCallback()
	{
		$entity = new Fixtures\RootEntity("Roman Štolpa se psychicky zhroutil.");
		$this->attachContainer($container = new EntityContainer($entity));

		$callback = $this->getMock(__NAMESPACE__ . '\CallbackMock', array('__invoke'));
		$callback->expects($this->once())
			->method('__invoke')
			->with($this->equalTo($this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity')))
			->will($this->returnValue($items = array(1 => 'Strhaná tvář', 2 => 'slzy v očích')));

		$select = $container->addSelect('children', 'Child', $callback);

		$this->mapper->loadControlItems();
		$this->assertEquals($items, $select->getItems());
	}



	public function testLoading_Controls()
	{
		$entity = new Fixtures\RootEntity("Chuck Tesla");
		$container = new EntityContainer($entity);
		$container->addText('name');

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->load();

		$this->assertEquals("Chuck Tesla", $container['name']->value);
	}



	public function testLoading_RelatedEntityValues()
	{
		$entity = new Fixtures\RootEntity("Dáda");
		$entity->daddy = new Fixtures\RelatedEntity("Motherfucker");

		$container = new EntityContainer($entity);
		$container->addText('name');
		$container['daddy'] = $daddy = new EntityContainer($this->mapper->getRelated($container, 'daddy'));
		$daddy->addText('name');

		$this->assertEmpty($container['name']->value);
		$this->assertEmpty($daddy['name']->value);

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->assign($entity->daddy, $daddy); // this is done in attached
		$this->mapper->load(); // form attached to presenter

		$this->assertEquals("Dáda", $container['name']->value);
		$this->assertEquals("Motherfucker", $daddy['name']->value);
	}



	public function testLoading_CreatingRelatedEntity()
	{
		$entity = new Fixtures\RootEntity("Dáda");
		$this->attachContainer($container = new EntityContainer($entity));
		$container->addText('name');

		// when requested relation, mapper tries to create the entity
		$this->assertNull($entity->daddy);
		$daddy = $container->addOne('daddy');
		$this->assertInstanceOf(__NAMESPACE__ . '\Fixtures\RelatedEntity', $entity->daddy);

		$daddy->addText('name');
		$this->mapper->load();

		$this->assertEquals("Dáda", $container['name']->value);
		$this->assertEmpty($daddy['name']->value);
	}



	public function testLoading_OnLoadEvent_Entity()
	{
		$entity = new Fixtures\RootEntity("Jessica Fletcher");
		$container = new EntityContainer($entity);
		$container->addText('name');

		$calls = array();
		$container->onLoad[] = function () use (&$calls) {
			$calls[] = func_get_args();
		};

		$this->mapper->assign($entity, $container);
		$this->mapper->load();

		$this->assertCount(1, $calls);

		$call = current($calls);
		$this->assertEquals(Nette\ArrayHash::from(array('name' => "Jessica Fletcher")), $call[0]);
		$this->assertSame($entity, $call[1]);
	}



	/**
	 * @param \Kdyby\Doctrine\Forms\IObjectContainer $container
	 * @param bool $submitted
	 *
	 * @return \Kdyby\Doctrine\Forms\Form
	 */
	private function attachContainer(Kdyby\Doctrine\Forms\IObjectContainer $container, $submitted = FALSE)
	{
		$form = new Form($this->getDoctrine(), NULL, $this->mapper);
		$form->addSubmit('send');
		if ($submitted !== NULL) {
			$form->setSubmittedBy($submitted !== FALSE ? $form['send'] : NULL);
		}
		$form['entity'] = $container;
		return $form;
	}



	/**
	 * @param \Kdyby\Doctrine\Forms\Form $form
	 *
	 * @return \Nette\Application\UI\Presenter|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachForm(Form $form)
	{
		$presenter = $this->getMock('Nette\Application\UI\Presenter', array(), array($this->getContext()));
		$form->setParent($presenter, 'form');
		return $presenter;
	}



	public function testLoading_RelatedCollection()
	{
		$entity = new Fixtures\RootEntity("Agáta Hanychová");
		$entity->children[] = new Fixtures\RelatedEntity("se otrávila jídlem");
		$entity->children[] = new Fixtures\RelatedEntity("Celé dny");
		$entity->children[] = new Fixtures\RelatedEntity("jenom zvrací");

		$form = new Form($this->getDoctrine(), $entity, $this->mapper);
		$form->addText('name');
		$form->addMany('children', function (EntityContainer $container) {
			$container->addText('name');
		});

		// attach to presenter
		$this->attachForm($form);

		// form attached to presenter
		$this->mapper->load();

		// check
		$this->assertCount(3, $form['children']->components);
		$this->assertEquals("Agáta Hanychová", $form['name']->value);
		$this->assertEquals(Nette\ArrayHash::from(array(
			array('name' => "se otrávila jídlem"),
			array('name' => "Celé dny"),
			array('name' => "jenom zvrací"),
		)), $form['children']->getValues());
	}



	public function testSaving_Controls()
	{
		$entity = new Fixtures\RootEntity();
		$container = new EntityContainer($entity);
		$container->addText('name')->setValue("Barbara Falgeová");

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->loadControlItems();
		$this->mapper->save(); // form attached to presenter

		$this->assertEquals("Barbara Falgeová", $entity->name);
	}



	public function testSaving_RelatedEntityValues()
	{
		$entity = new Fixtures\RootEntity();
		$entity->daddy = new Fixtures\RelatedEntity();

		$container = new EntityContainer($entity);
		$container->addText('name')->setValue("Eva Herzigová");
		$container['daddy'] = $daddy = new EntityContainer($this->mapper->getRelated($container, 'daddy'));
		$daddy->addText('name')->setValue("Gregoriem Marsiajem");

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->assign($entity->daddy, $daddy);
		$this->mapper->save(); // form attached to presenter

		$this->assertEquals("Eva Herzigová", $entity->name);
		$this->assertEquals("Gregoriem Marsiajem", $entity->daddy->name);
	}



	public function testSaving_OnSaveEvent_Entity()
	{
		$entity = new Fixtures\RootEntity();
		$container = new EntityContainer($entity);
		$container->addText('name')->setValue("Hanka Mašlíková");

		$calls = array();
		$container->onSave[] = function () use (&$calls) {
			$calls[] = func_get_args();
		};

		$this->mapper->assign($entity, $container);
		$this->mapper->save();

		$this->assertCount(1, $calls);

		$call = current($calls);
		$this->assertEquals(Nette\ArrayHash::from(array('name' => "Hanka Mašlíková")), $call[0]);
		$this->assertSame($container, $call[1]);
	}



	/**
	 * @param object $entity
	 *
	 * @return array
	 */
	private function prepareChildrenContainer($entity)
	{
		$form = new Form($this->getDoctrine(), $entity, $this->mapper);
		$form->addText('name');
		$form->addMany('children', function(EntityContainer $container) {
			$container->addText('name');
		});
		return $form;
	}


	/**
	 * @return array
	 */
	public function dataSaving_RelatedCollection()
	{
		$entity = new Fixtures\RootEntity("Podívejte se");
		$entity->children[] = new Fixtures\RelatedEntity("na zapomenuté záběry");
		$entity->children[] = new Fixtures\RelatedEntity("nahé Marilyn Monroe");
		$entity->children[] = new Fixtures\RelatedEntity("natočené těsně před smrtí");

		return array(
			array($entity)
		);
	}



	/**
	 * @dataProvider dataSaving_RelatedCollection
	 *
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $entity
	 */
	public function testSaving_RelatedCollection_UpdatingExisting(Fixtures\RootEntity $entity)
	{
		$this->getDao($entity)->save($entity);
		$form = $this->prepareChildrenContainer($entity);

		// attach & save
		$this->submitForm($form, array('children' => array(
			array('id' => 1, 'name' => $a = "Nádherná Vignerová"),
			array('id' => 2, 'name' => $b = "se chystá opustit Ujfalušiho"),
			array('id' => 3, 'name' => $c = "Víme proč")
		), 'name' => "krysy na hotelovém pokoji"));

		$this->mapper->save();

		// check
		$this->assertEquals("krysy na hotelovém pokoji", $entity->name);
		$this->assertCount(3, $entity->children);
		$this->assertEquals($a, $entity->children[0]->name);
		$this->assertEquals($b, $entity->children[1]->name);
		$this->assertEquals($c, $entity->children[2]->name);
	}



	/**
	 * @dataProvider dataSaving_RelatedCollection
	 *
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $entity
	 */
	public function testSaving_RelatedCollection_Appending(Fixtures\RootEntity $entity)
	{
		$this->getDao($entity)->save($entity);

		$this->fail();
	}



	/**
	 * @dataProvider dataSaving_RelatedCollection
	 *
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $entity
	 */
	public function testSaving_RelatedCollection_RemovingSome(Fixtures\RootEntity $entity)
	{
		$this->getDao($entity)->save($entity);

		$this->fail();
	}



	/**
	 * @dataProvider dataSaving_RelatedCollection
	 *
	 * @param \Kdyby\Tests\Doctrine\Forms\Fixtures\RootEntity $entity
	 */
	public function testSaving_RelatedCollection_RemovingSomeAndAddingNew(Fixtures\RootEntity $entity)
	{
		$this->getDao($entity)->save($entity);

		$this->fail();
	}



	public function testSaving_RelatedCollection_CreatingNew()
	{
		$entity = new Fixtures\RootEntity("Marilyn Monroe");
		$this->getDao($entity)->save($entity);

		$this->fail();
	}

}