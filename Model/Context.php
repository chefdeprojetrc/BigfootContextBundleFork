<?php

namespace Bigfoot\Bundle\ContextBundle\Model;

use Bigfoot\Bundle\ContextBundle\Exception\ContextNotFoundException;
use Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class Context
 * @package Bigfoot\Bundle\ContextBundle\Model
 */
class Context
{
    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException
     */
    public function getContext($name)
    {
        $context = $this->getContextConfiguration($name);

        foreach ($context['loaders'] as $loader) {
            $loader = $this->container->get($loader);
            if (!$loader instanceof ContextLoaderInterface) {
                throw new NotImplementedException('A ContextLoader service must implement the Bigfoot\Bundle\ContextBundle\Model\ContextLoaderInterface interface.');
            }

            if ($value = $loader->getValue()) {
                return $value;
            }
        }

        return $context['values'][$context['default_value']];
    }

    /**
     * @param $name
     * @return array
     */
    public function getContextValues($name)
    {
        $context = $this->getContextConfiguration($name);
        $values = $context['values'];

        $toReturn = array();
        foreach ($values as $value) {
            $toReturn[$value['value']] = $value['label'];
        }

        return $toReturn;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\ContextNotFoundException
     */
    private function getContextConfiguration($name)
    {
        $contextConfiguration = $this->container->getParameter('bigfoot_contexts');
        if (!array_key_exists($name, $contextConfiguration)) {
            throw new ContextNotFoundException(sprintf('The context %s is undefined. Please add it to the bigfoot_context.contexts configuration in your config.yml file.', $name));
        }

        return $contextConfiguration[$name];
    }
}
