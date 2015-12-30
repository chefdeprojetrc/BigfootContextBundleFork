<?php

namespace Bigfoot\Bundle\ContextBundle\Twig\Extension;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig_Extension;
use Twig_SimpleFunction;

use Bigfoot\Bundle\ContentBundle\Entity\Page;
use Bigfoot\Bundle\ContentBundle\Entity\Sidebar;
use Bigfoot\Bundle\ContentBundle\Entity\Page\Block as PageBlock;
use Bigfoot\Bundle\ContentBundle\Entity\Page\Sidebar as PageSidebar;
use Bigfoot\Bundle\ContentBundle\Entity\Block;
use Bigfoot\Bundle\ContextBundle\Service\ContextService;

/**
 * ContextExtension
 */
class ContextExtension extends Twig_Extension
{
    private $contextService;

    /**
     * Construct ContentExtension
     */
    public function __construct(ContextService $contextService)
    {
        $this->contextService = $contextService;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('get_context', array($this, 'getContext')),
            new Twig_SimpleFunction('bigfoot_default_front_locale', array($this, 'getDefaultFrontLocale')),
        );
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return mixed
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException
     * @throws \Exception
     */
    public function getContext($name, $value = null)
    {
        return $this->contextService->get($name, $value);
    }

    /**
     * @return string
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException
     * @throws \Exception
     */
    public function getDefaultFrontLocale()
    {
        return $this->contextService->getDefaultFrontLocale();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'bigfoot_context';
    }
}
