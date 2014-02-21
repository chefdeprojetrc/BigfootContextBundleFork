<?php

namespace Bigfoot\Bundle\ContextBundle\Listener;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Menu Listener
 */
class MenuListener implements EventSubscriberInterface
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
            MenuEvent::GENERATE_MAIN => 'onGenerateMain',
        );
    }

    /**
     * @param GenericEvent $event
     */
    public function onGenerateMain(GenericEvent $event)
    {
        $menu          = $event->getSubject();
        $structureMenu = $menu->getChild('structure');

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $structureMenu->addChild(
                'context',
                array(
                    'label'  => 'Context',
                    'route'  => 'admin_context',
                    'linkAttributes' => array(
                        'icon' => 'globe',
                    )
                )
            );
        }
    }
}