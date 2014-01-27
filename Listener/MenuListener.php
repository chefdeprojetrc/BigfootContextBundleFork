<?php

namespace Bigfoot\Bundle\ContextBundle\Listener;

use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;

/**
 * Menu Listener
 */
class MenuListener implements EventSubscriberInterface
{
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