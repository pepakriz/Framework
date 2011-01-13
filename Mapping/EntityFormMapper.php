<?php

namespace Kdyby\Doctrine\Mapping;

use Kdyby;
use Nette;
use Doctrine;



class EntityFormMapper extends Nette\Object implements Kdyby\Doctrine\Mapping\IFormMapper
{

	/** @var Doctrine\ORM\EntityManager */
	private $entityManager;



	/**
	 * @param Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(Doctrine\ORM\EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}



	/**
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 * @return array
	 */
	public function toArray($entity)
	{
		$data = array();

		$class = $this->entityManager->getClassMetadata(get_class($entity));
		$id = $class->getIdentifierValues($entity); // !$id === NEW

		foreach ($class->reflFields as $name => $prop) {
			if ( ! isset($class->associationMappings[$name])) {
				if ( ! $class->isIdentifier($name)) {
					$data[$name] = $prop->getValue($entity);
				}

			} else {
				$assoc2 = $class->associationMappings[$name];
				if ($assoc2['type'] & ClassMetadata::TO_ONE) {
					$other = $prop->getValue($entity);
					if ($other === null) {
						$data[$name] = NULL;

					} elseif ($other instanceof Proxy && !$other->__isInitialized__) {
						$data[$name] = NULL;

					} elseif ( ! $assoc2['isCascadeMerge']) {
						if ($this->getEntityState($other, self::STATE_DETACHED) == self::STATE_MANAGED) {
							$data[$name] = $other;

						} else {
							$targetClass = $this->em->getClassMetadata($assoc2['targetEntity']);
							$id = $targetClass->getIdentifierValues($other);
							$proxy = $this->em->getProxyFactory()->getProxy($assoc2['targetEntity'], $id);
							$prop->setValue($managedCopy, $proxy);
							$this->registerManaged($proxy, $id, array());
						}
					}

				} else {
					$mergeCol = $prop->getValue($entity);
					if ($mergeCol instanceof PersistentCollection && !$mergeCol->isInitialized()) {
						// do not merge fields marked lazy that have not been fetched.
						// keep the lazy persistent collection of the managed copy.
						$data[$name] = NULL;
						continue;
					}

					foreach ($mergeCol->getValues() as $value) {
						try {
							$colClass = $this->entityManager->getClassMetadata(get_class($value));
						} catch (Doctrine\ORM\Mapping\MappingException $e) { 
							$colClass = FALSE;
						}

						if (is_object($value) && $colClass) {
							$data[$name][] = $this->toArray($value);
							continue;
						}

						$data[$name][] = $value;
					}
				}
			}
		}

		return $data;
	}



	/**
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 * @return int|array
	 */
	protected function entityToIdentifikator($entity)
	{
		$class = $this->entityManager->getClassMetadata(get_class($entity));
		$id = $class->getIdentifierValues($entity); // !$id === NEW

		return $class->isIdentifierComposite ? $id : current($id);
	}



	/**
	 * @param int|array $id
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 * @return Kdyby\Doctrine\BaseEntity
	 */
	protected function identifikatorToEntity($id, $entity)
	{
		return $this->entityManager->getRepository(get_class($entity))->find($id);
	}



	/**
	 * @param array $array
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 * @return Kdyby\Doctrine\BaseEntity
	 *
	 * @todo implement!
	 */
	public function toEntity($array, $entity)
	{
		return $entity;
	}

}