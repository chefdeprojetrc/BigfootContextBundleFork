<?php

namespace Bigfoot\Bundle\ContextBundle\Listener;

use Bigfoot\Bundle\CoreBundle\Event\MenuEvent;
use Bigfoot\Bundle\CoreBundle\Theme\Menu\Item;

/**
 * Adds the context submenu into the sidebar.
 *
 * Class MenuListener
 * @package Bigfoot\Bundle\ContextBundle\Listener
 */
class MenuListener
{
    /**
     * @param MenuEvent $event
     */
    public function onMenuGenerate(MenuEvent $event)
    {
        $menu = $event->getMenu();

        if ($menu->getName() == 'sidebar_menu')
        {
            $menu->addOnItem('sidebar_settings', new Item('sidebar_settings_context', 'Context management', 'admin_context'));
        }
    }
}
