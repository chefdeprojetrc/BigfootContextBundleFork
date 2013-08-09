<?php

namespace Bigfoot\Bundle\ContextBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContextualizedEntity
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\ContextBundle\Entity\ContextualizedEntityRepository")
 */
class ContextualizedEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entity", type="string", length=255)
     */
    private $entity;

    /**
     * @var integer
     *
     * @ORM\Column(name="entity_id", type="integer")
     */
    private $entityId;

    /**
     * @var array
     *
     * @ORM\Column(name="context_values", type="array")
     */
    private $contextValues;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entity
     *
     * @param string $entity
     * @return ContextualizedEntity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    
        return $this;
    }

    /**
     * Get entity
     *
     * @return string 
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set entityId
     *
     * @param integer $entityId
     * @return ContextualizedEntity
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;
    
        return $this;
    }

    /**
     * Get entityId
     *
     * @return integer 
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * Set contextValues
     *
     * @param array $contextValues
     * @return ContextualizedEntity
     */
    public function setContextValues($contextValues)
    {
        $this->contextValues = $contextValues;
    
        return $this;
    }

    /**
     * Get contextValues
     *
     * @return array 
     */
    public function getContextValues()
    {
        return $this->contextValues;
    }
}
