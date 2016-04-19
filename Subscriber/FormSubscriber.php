<?php

namespace Bigfoot\Bundle\ContextBundle\Subscriber;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactory;

use Bigfoot\Bundle\CoreBundle\Event\FormEvent;
use Bigfoot\Bundle\CoreBundle\Annotation\Bigfoot\Context;
use Bigfoot\Bundle\ContextBundle\Service\ContextService;
use Bigfoot\Bundle\ContextBundle\Form\Type\ContextType;

/**
 * Form Subscriber
 */
class FormSubscriber implements EventSubscriberInterface
{
    private $formFactory;
    private $contextService;

    /**
     * @param Array $formFactory
     * @param Array $contextService
     */
    public function __construct(FormFactory $formFactory, ContextService $contextService)
    {
        $this->formFactory    = $formFactory;
        $this->contextService = $contextService;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvent::CREATE => 'onCreate',
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function onCreate(GenericEvent $event)
    {
        $form = $event->getSubject();

        if (is_object($form->getData())) {
            $entityClass = $this->contextService->resolveEntityClass(get_class($form->getData()));
            $contexts    = $this->contextService->getEntityContexts($form->getData());

            if ($contexts) {
                $contextForm = $this->formFactory->create(
                    ContextType::class,
                    $form->getData(),
                    array(
                        'entityClass' => $entityClass,
                        'contexts'    => $contexts,
                    )
                );

                $form->add($contextForm);
            }
        }
    }
}
