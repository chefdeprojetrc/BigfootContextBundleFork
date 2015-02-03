<?php

namespace Bigfoot\Bundle\ContextBundle\Service;

use Doctrine\Common\Annotations\AnnotationReader;

use Bigfoot\Bundle\ContextBundle\Loader\LoaderInterface;
use Bigfoot\Bundle\ContextBundle\Loader\LoaderChain;
use Bigfoot\Bundle\ContextBundle\Exception\NotFoundException;
use Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException;

/**
 * Class Context
 * @package Bigfoot\Bundle\ContextBundle\Service
 */
class ContextService
{
    private $loaderChain;
    private $loaders;
    private $contexts;
    private $entities;
    private $queued;

    /**
     * Construct ContextService
     *
     * @param LoaderChain $loaderChain
     * @param Array $contexts
     * @param Array $entities
     */
    public function __construct(LoaderChain $loaderChain, $contexts, $entities)
    {
        $this->loaderChain = $loaderChain;
        $this->loaders     = $loaderChain->getLoaders();
        $this->contexts    = $contexts;
        $this->entities    = $entities;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException
     */
    public function get($name, $returnConfiguration = false, $value = null)
    {
        $context = $this->getConfig($name);

        if ($value !== null) {
            $contextConfiguration = null;

            foreach ($context['values'] as $key => $contextValue) {
                if ($contextValue['value'] == $value) {
                    $contextConfiguration        = $contextValue;
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
            $loader = $this->loaders[$loader];

            if (!$loader instanceof LoaderInterface) {
                throw new NotImplementedException('A ContextLoader service must implement the Bigfoot\Bundle\ContextBundle\Loader\LoaderInterface.');
            }

            $fContext = $loader->getValue();

            foreach ($context['values'] as $key => $contextValue) {
                if ($contextValue['value'] == $fContext['value']) {
                    $fContext        = $contextValue;
                    $fContext['key'] = $key;
                    break;
                }
            }

            if ($loader->getValue()) {
                return $returnConfiguration ? $fContext : $fContext['value'];
            }
        }

        $contextValues = $context['values'][$context['default_value']];
        $contextValues['key'] = $context['default_value'];

        return $returnConfiguration ? $contextValues : $contextValues['value'];
    }

    /**
     * Alias of {@link get()}.
     *
     * @deprecated Deprecated since version 1.x, to be removed in 2.x Use {@link get()} instead.
     */
    public function getContext($name, $value = null)
    {
        trigger_error(
            'getContext() is deprecated since version 1.x and will be removed in 2.x Use get() instead.',
            E_USER_DEPRECATED
        );

        return $this->get($name, $value = null);
    }

    /**
     * @param $name
     * @return array
     */
    public function getValues($name)
    {
        $context  = $this->getConfig($name);
        $values   = $context['values'];
        $toReturn = array();

        foreach ($values as $value) {
            $toReturn[$value['value']] = $value['label'];
        }

        return $toReturn;
    }

    /**
     * Alias of {@link getValues()}.
     *
     * @deprecated Deprecated since version 1.x, to be removed in 2.x Use {@link getValues()} instead.
     */
    public function getContextValues($name)
    {
        trigger_error(
            'getContextValues() is deprecated since version 1.x and will be removed in 2.x Use getValues() instead.',
            E_USER_DEPRECATED
        );

        return $this->getValues($name);
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotFoundException
     */
    private function getConfig($name)
    {
        if (is_object($name)) {
            $name = $name->value;
        }
        if (!array_key_exists($name, $this->contexts)) {
            throw new NotFoundException(sprintf('The context %s is undefined. Please add it to the bigfoot_context.contexts configuration in your config.yml file.', $name));
        }

        return $this->contexts[$name];
    }

    /**
     * Alias of {@link getConfig()}.
     *
     * @deprecated Deprecated since version 1.x, to be removed in 2.x Use {@link getConfig()} instead.
     */
    private function getContextConfiguration($name)
    {
        trigger_error(
            'getContextConfiguration() is deprecated since version 1.x and will be removed in 2.x Use getConfig() instead.',
            E_USER_DEPRECATED
        );

        return $this->getConfig($name);
    }

    public function getEntities()
    {
        return $this->entities;
    }

    public function getContexts()
    {
        return $this->contexts;
    }

    public function getEntityContexts($entity)
    {
        $entityClass = (is_object($entity)) ? get_class($entity) : $entity;
        $reflClass   = new \ReflectionClass($entityClass);
        $contexts    = $this->getContextAnnotations($reflClass);

        if (!$contexts) {
            $entities = $this->getEntities();

            foreach ($entities as $key => $entity) {
                if ($entityClass == $entity['class'] || get_parent_class($entityClass) == $entity['class']) {
                    $contexts = array_merge($contexts, $entity['contexts']);
                }
            }
        }

        return $this->objectToArray($contexts);
    }

    /**
     * @param $object
     * @return mixed
     */
    function objectToArray($object)
    {
        $array = array();

        if (is_array($object) || is_object($object)) {
            foreach ($object as $key => $value) {
                $array[$key] = (is_array($value) || is_object($value)) ? $this->objectToArray($value): $value;
            }
        }

        return $array;
    }

    public function resolveEntityClass($entityClass)
    {
        $entities = $this->getEntities();

        foreach ($entities as $key => $entity) {
            if (get_parent_class($entityClass) == $entity['class']) {
                $entityClass = $entity['class'];
            }
        }

        return $entityClass;
    }

    public function addToQueue($entityClass, $values)
    {
        $this->queued[$entityClass] = array(
            'entityClass'    => $entityClass,
            'context_values' => $values,
        );
    }

    /**
     * Returns all BigfootContext annotations
     *
     * @param \ReflexionClass $reflClass
     * @return array
     */
    public function getContextAnnotations($reflClass)
    {
        $reader = new AnnotationReader();
        $annotations = $reader->getClassAnnotations($reflClass);
        return array_values(array_filter($annotations, function($annotation) {
            return get_class($annotation) == 'Bigfoot\Bundle\ContextBundle\Annotation\Bigfoot\Context';
        }));
    }

    public function getQueued()
    {
        return $this->queued;
    }

    public function clearQueue()
    {
        return $this->queued = array();
    }
}
