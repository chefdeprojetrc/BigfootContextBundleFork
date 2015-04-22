<?php

namespace Bigfoot\Bundle\ContextBundle\Subscriber;

use Bigfoot\Bundle\ContextBundle\Entity\ContextRepository;
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

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity          = $args->getEntity();
        $entityClass     = $this->contextService->resolveEntityClass(get_class($entity));
        $contexts        = $this->contextService->getEntityContexts($entity);

        if ($contexts) {
            /** @var ContextRepository $contextRepo */
            $contextRepo   = $args->getEntityManager()->getRepository('BigfootContextBundle:Context');
            $context       = $contextRepo->findOneByEntityIdEntityClass($entity->getId(), $entityClass);
            $queued        = $this->contextService->getQueued();
            if (isset($queued[$entityClass])) {
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
    }

    /**
     * @param PostFlushEventArgs $args
     */
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
                            $contextRepo   = $args->getEntityManager()->getRepository('BigfootContextBundle:Context');
                            /** @var ContextRepository $contextRepo */
                            $context       = $contextRepo->findOneByEntityIdEntityClass($entityId, $entityClass);

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

    /**
     * @param $entityId
     * @param $entityClass
     * @param $contextValues
     * @return Context
     */
    public function createContext($entityId, $entityClass, $contextValues)
    {
        $context = new Context();

        return $context
            ->setEntityId($entityId)
            ->setEntityClass($entityClass)
            ->setContextValues($contextValues)
        ;
    }
}