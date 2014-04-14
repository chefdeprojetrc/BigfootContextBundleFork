<?php

namespace Bigfoot\Bundle\ContextBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr;

class ContextManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateContext($entity)
    {
        return $this->entityManager->getUnitOfWork()->scheduleForUpdate($entity);
    }
}
