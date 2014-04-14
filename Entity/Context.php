<?php

namespace Bigfoot\Bundle\ContextBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Context
 *
 * @ORM\Table(name="bigfoot_context")
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\ContextBundle\Entity\ContextRepository")
 */
class Context
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entity_id", type="integer")
     */
    protected $entityId;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_class", type="string", length=255)
     */
    protected $entityClass;

    /**
     * @var array
     *
     * @ORM\Column(name="context_values", type="array")
     */
    protected $contextValues;

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
     * Set entityId
     *
     * @param integer $entityId
     * @return Context
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
     * Set entityClass
     *
     * @param string $entityClass
     * @return Context
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entityClass
     *
     * @return string
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * Set contextValues
     *
     * @param array $contextValues
     * @return Context
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
