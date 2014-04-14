<?php

namespace Bigfoot\Bundle\ContextBundle\Subscriber;

use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;

use Bigfoot\Bundle\CoreBundle\Event\FormEvent;
use Bigfoot\Bundle\ContextBundle\Entity\Context;
use Bigfoot\Bundle\ContextBundle\Service\ContextService;

/**
 * Doctrine Subscriber
 */
class DoctrineSubscriber implements EventSubscriber
{
    /**
     * @var ContextService
     */
    protected $contextService;

    /**
     * @param ContextService $contextService
     */
    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postUpdate',
            'postFlush',
        );
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity          = $args->getEntity();
        $entityClass     = $this->contextService->resolveEntityClass(get_class($entity));
        $contexts        = $this->contextService->getEntityContexts($entity);
        $contextEntities = $this->contextService->getEntities();

        if ($contexts) {
            $context       = $args->getEntityManager()->getRepository('BigfootContextBundle:Context')->findOneByEntityIdEntityClass($entity->getId(), $entityClass);
            $queued        = $this->contextService->getQueued();
            $contextValues = $queued[$entityClass]['context_values'];

            if ($context && $queued) {
                $context->setContextValues($contextValues);
            } elseif (!$context && $contextValues) {
                $context = $this->createContext($entity->getId(), $entityClass, $contextValues);
            }

            if ($context) {
                $args->getEntityManager()->persist($context);
                $args->getEntityManager()->flush();
            }
        }
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();

        foreach ($entityManager->getUnitOfWork()->getIdentityMap() as $key => $entities) {
            foreach ($entities as $entityId => $entity) {
                $changeSet   = $entityManager->getUnitOfWork()->getEntityChangeSet($entity);
                $entityClass = $this->contextService->resolveEntityClass(get_class($entity));
                $contexts    = $this->contextService->getEntityContexts($entityClass);

                if (count($contexts) && count($changeSet)) {
                    $queued = $this->contextService->getQueued();

                    if (count($queued)) {
                        if (isset($queued[$entityClass])) {
                            $currentEntity = $queued[$entityClass];
                            $context       = $args->getEntityManager()->getRepository('BigfootContextBundle:Context')->findOneByEntityIdEntityClass($entityId, $entityClass);

                            if (!$context) {
                                $context = $this->createContext($entityId, $entityClass, $currentEntity['context_values']);
                            }

                            $entityManager->persist($context);
                            $this->contextService->clearQueue();
                            $entityManager->flush();
                        }
                    }
                }
            }
        }
    }

    public function createContext($entityId, $entityClass, $contextValues)
    {
        $context = new Context();

        return $context
            ->setEntityId($entityId)
            ->setEntityClass($entityClass)
            ->setContextValues($contextValues);
    }
}