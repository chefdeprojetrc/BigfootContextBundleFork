<?php

namespace Bigfoot\Bundle\ContextBundle\Entity;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * ContextRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContextRepository extends EntityRepository
{
    /** @var SessionInterface */
    public $session;

    /**
     * @param SessionInterface $session
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @param $class
     * @param array $definedContext
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createContextQueryBuilder($class, $definedContext = array())
    {
        $chosenContext = $this->session->get('bigfoot/context/chosen_contexts');
        $contextValues = $this->session->get('bigfoot/context/allowed_contexts');

        if ($chosenContext) {
            $contextValues = $chosenContext;
        } elseif (count($definedContext)) {
            $contextValues = $definedContext;
        }

        $queryBuilder = $this->getEntityManager()->getRepository($class)->createQueryBuilder('e');
        $regex        = array();
        $orX          = array();

        if (count($contextValues)) {
            $queryBuilder = $queryBuilder
                ->leftJoin('BigfootContextBundle:Context', 'c', 'WITH',
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq('e.id', 'c.entityId'),
                        $queryBuilder->expr()->eq('c.entityClass', "'$class'")
                    )
                );

            foreach ($contextValues as $context => $values) {
                foreach ($values as $key => $value) {
                    $regex[] = new Expr\Comparison('REGEXP(c.contextValues, \'[a-z0-9:;\{}\"]*'.$context.'[a-z0-9:;\{\"]*'.$value.'.*\}\')', Expr\Comparison::EQ, 1);
                }

                $regex[] = new Expr\Comparison('REGEXP(c.contextValues, \'[a-z0-9:;\{}\"]*'.$context.'[a-z0-9:;\"]*\{\}\')', Expr\Comparison::EQ, 1);
                $regex[] = $queryBuilder->expr()->isNull('c.contextValues');

                $orX[]   = new Expr\Orx($regex);
            }

            $andX = new Expr\AndX($orX);

            $queryBuilder->where('('.$andX.')');
        }

        return $queryBuilder;
    }

    public function findOneByEntityIdEntityClass($entityId, $entityClass)
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.entityId = :entityId')
            ->andWhere('c.entityClass = :entityClass')
            ->setParameter('entityId', $entityId)
            ->setParameter('entityClass', $entityClass)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
