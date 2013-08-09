<?php

namespace Bigfoot\Bundle\ContextBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContextualizableEntities
 *
 * @ORM\Table("contextualizable_entities")
 * @ORM\Entity(repositoryClass="Bigfoot\Bundle\ContextBundle\Entity\ContextualizableEntitiesRepository")
 */
class ContextualizableEntities
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
     * @ORM\Column(name="context", type="string", length=255, unique=true)
     */
    private $context;

    /**
     * @var array
     *
     * @ORM\Column(name="entities", type="array")
     */
    private $entities;


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
     * Set context
     *
     * @param string $context
     * @return ContextualizableEntity
     */
    public function setContext($context)
    {
        $this->context = $context;
    
        return $this;
    }

    /**
     * Get context
     *
     * @return string 
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set entities
     *
     * @param array $entities
     * @return ContextualizableEntity
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
    
        return $this;
    }

    /**
     * Get entities
     *
     * @return array 
     */
    public function getEntities()
    {
        return $this->entities;
    }
}
