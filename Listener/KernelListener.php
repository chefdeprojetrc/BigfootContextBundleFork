<?php

namespace Bigfoot\Bundle\ContextBundle\Listener;

use Bigfoot\Bundle\ContextBundle\Exception\ContextNotFoundException;
use Bigfoot\Bundle\ContextBundle\Model\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class KernelListener
{
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        try {
            $lang = $this->context->getContext('language');
            $event->getRequest()->setLocale($lang['value']);
        } catch (ContextNotFoundException $e) {
            // Language context doesn't exist
        }
    }
}
