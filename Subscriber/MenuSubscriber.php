<?php

namespace Bigfoot\Bundle\ContextBundle\Subscriber;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

/**
 * Menu Subscriber
 */
class MenuSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $security;

    /**
     * @param SecurityContextInterface $security
     */
    public function __construct(SecurityContextInterface $security)
    {
        $this->security = $security;
    }

    /**
     * Get subscribed events
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            MenuEvent::GENERATE_MAIN => array('onGenerateMain', 4)
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function onGenerateMain(GenericEvent $event)
    {
        $builder = $event->getSubject();

        if (!$builder->childExists('structure')) {
            $builder
                ->addChild(
                    'structure',
                    array(
                        'label'          => 'Structure',
                        'url'            => '#',
                        'linkAttributes' => array(
                            'class' => 'dropdown-toggle',
                            'icon'  => 'building',
                        )
                    ),
                    array(
                        'children-attributes' => array(
                            'class' => 'submenu'
                        )
                    )
                );
        }

        $builder
            ->addChildFor(
                'structure',
                'structure_context',
                array(
                    'label'          => 'Context',
                    'url'            => '#',
                    'linkAttributes' => array(
                        'icon' => 'globe',
                    )
                )
            );
    }
}
