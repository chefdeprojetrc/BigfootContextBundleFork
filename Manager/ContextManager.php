<?php

namespace Bigfoot\Bundle\ContextBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;
use Doctrine\Common\Annotations\AnnotationReader;

class ContextManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $contextualizedEntities;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager, $contextualizedEntities)
    {
        $this->entityManager          = $entityManager;
        $this->contextualizedEntities = $contextualizedEntities;
    }

    public function updateContext($entity)
    {
        return $this->entityManager->getUnitOfWork()->scheduleForUpdate($entity);
    }

    /**
     * Returns true is $entityClass is contextualizable on context $context
     *
     * Checks for annotations on entity and bigfoot_context configuration
     *
     * @param string $entityClass
     * @param string $context
     */
    public function isEntityContextualizable($entityClass, $context)
    {
        // Annotations
        $reflClass   = new \ReflectionClass($entityClass);
        $reader      = new AnnotationReader();
        $annotations = $reader->getClassAnnotations($reflClass);

        foreach ($annotations as $annotation) {
            if (get_class($annotation) == 'Bigfoot\Bundle\ContextBundle\Annotation\Bigfoot\Context') {
                return true;
            }
        }

        // Configuration
        foreach ($this->contextualizedEntities as $contextualizedEntity) {
            foreach ($contextualizedEntity['contexts'] as $entityContext) {
                if ($entityContext['value'] == $context) {
                    return true;
                }
            }
        }

        return false;
    }
}
