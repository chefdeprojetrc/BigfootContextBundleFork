<?php

namespace Bigfoot\Bundle\ContextBundle\Model;

use Symfony\Component\DependencyInjection\Container;

abstract class AbstractContextLoader implements ContextLoaderInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function getValueForKey($key)
    {
        $contextConfiguration = $this->container->getParameter('bigfoot_contexts');

        if (array_key_exists($key, $contextConfiguration[$this->getContextName()]['values'])) {
            return $contextConfiguration[$this->getContextName()]['values'][$key];
        }
    }

    public abstract function getContextName();
}