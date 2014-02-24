<?php

namespace Bigfoot\Bundle\ContextBundle\Model;

use Symfony\Component\DependencyInjection\Container;

/**
 * Class AbstractContextLoader
 * @package Bigfoot\Bundle\ContextBundle\Model
 */
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

    /**
     * @param $key
     * @return mixed
     */
    protected function getValueForKey($key)
    {
        $contextConfiguration = $this->container->getParameter('bigfoot_contexts');

        if (array_key_exists($key, $contextConfiguration[$this->getContextName()]['values'])) {
            $contextValues = $contextConfiguration[$this->getContextName()]['values'][$key];
            $contextValues['key'] = $key;
            return $contextValues;
        }
    }

    /**
     * @return mixed
     */
    public abstract function getContextName();
}
