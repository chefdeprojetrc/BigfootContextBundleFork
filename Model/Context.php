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
    public function getContext($name, $value = null)
    {
        $context = $this->getContextConfiguration($name);

        if ($value !== null) {
            $contextConfiguration = null;
            foreach ($context['values'] as $key => $contextValue) {
                if ($contextValue['value'] == $value) {
                    $contextConfiguration = $contextValue;
                    $contextConfiguration['key'] = $key;
                    break;
                }
            }

            if (!$contextConfiguration) {
                throw new \Exception(sprintf('Context value "%s" for context "%s" was not found. Allowed context values are (%s)', $value, $name, implode(', ', array_keys($context['values']))));
            }

            return $contextConfiguration;
        }

        foreach ($context['loaders'] as $loader) {
            $loader = $this->container->get($loader);
            if (!$loader instanceof ContextLoaderInterface) {
                throw new NotImplementedException('A ContextLoader service must implement the Bigfoot\Bundle\ContextBundle\Model\ContextLoaderInterface interface.');
            }

            if ($value = $loader->getValue()) {
                return $value;
            }
        }

        $contextValues = $context['values'][$context['default_value']];
        $contextValues['key'] = $context['default_value'];

        return $contextValues;
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
