<?php

namespace Bigfoot\Bundle\ContextBundle\Service;

use Doctrine\Common\Annotations\AnnotationReader;

use Bigfoot\Bundle\ContextBundle\Loader\LoaderInterface;
use Bigfoot\Bundle\ContextBundle\Loader\LoaderChain;
use Bigfoot\Bundle\ContextBundle\Exception\NotFoundException;
use Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException;
use Bigfoot\Bundle\ContextBundle\Exception\RequestNotSetException;
use Bigfoot\Bundle\ContextBundle\Loader\AbstractLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelInterface;
/**
 * Class Context
 *
 * @package Bigfoot\Bundle\ContextBundle\Service
 */
class ContextService
{
    /** @var \Bigfoot\Bundle\ContextBundle\Loader\LoaderChain */
    private $loaderChain;

    /** @var array */
    private $loaders = array();

    /** @var array */
    private $contexts = array();

    /** @var array */
    private $entities = array();

    /** @var array */
    private $queued = array();

    /** @var array  */
    private $computedContexts = array();

    /** @var KernelInterface  */
    private $kernel;

    /** @var  Request */
    private $request;

    /**
     * Construct ContextService
     *
     * @param LoaderChain $loaderChain
     * @param array $contexts
     * @param array $entities
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     */
    public function __construct(LoaderChain $loaderChain, $contexts, $entities, KernelInterface $kernel, RequestStack $requestStack)
    {
        $this->loaderChain = $loaderChain;
        $this->loaders     = $loaderChain->getLoaders();
        $this->contexts    = $contexts;
        $this->entities    = $entities;
        $this->kernel      = $kernel;
        $this->request     = $requestStack->getCurrentRequest();
    }

    /**
     * @param      $name
     * @param bool $returnConfiguration
     * @param null $value
     *
     * @return mixed|null
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotFoundException
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException
     * @throws \Exception
     */
    public function get($name, $returnConfiguration = false, $value = null)
    {
        $context = $this->getConfig($name);

        if (null === $this->request) {
            throw new RequestNotSetException("The request needs to be set - please use ContextService::setRequest");
        }

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
                throw new \Exception(
                    sprintf(
                        'Context value "%s" for context "%s" was not found. Allowed context values are (%s)',
                        $value,
                        $name,
                        implode(', ', array_keys($context['values']))
                    )
                );
            }

            return $contextConfiguration;
        }

        foreach ($context['loaders'] as $loader) {
            $loader = $this->loaders[$loader];

            if (!$loader instanceof LoaderInterface) {
                throw new NotImplementedException(
                    'A ContextLoader service must implement the Bigfoot\Bundle\ContextBundle\Loader\LoaderInterface.'
                );
            }

            if ($loader instanceof AbstractLoader) {
                $loader->setRequest($this->request);
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

        $contextValues        = $context['values'][$context['default_value']];
        $contextValues['key'] = $context['default_value'];

        return $returnConfiguration ? $contextValues : $contextValues['value'];
    }

    /**
     * Alias of {@link get()}.
     *
     * @param      $name
     * @param null $value
     *
     * @return mixed|null
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotImplementedException
     * @throws \Exception
     * @deprecated Deprecated since version 1.x, to be removed in 2.x Use {@link get()} instead.
     */
    public function getContext($name, $value = null)
    {
        trigger_error(
            'getContext() is deprecated since version 1.x and will be removed in 2.x Use get() instead.',
            E_USER_DEPRECATED
        );

        return $this->get($name, $value);
    }

    /**
     * @param $name
     *
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
     * @return string
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotFoundException
     */
    public function getDefaultFrontLocale()
    {
        if (!in_array($this->kernel->getEnvironment(), array('admin', 'admin_dev'))) {
            return $this->get('language');
        }

        $config = $this->get('language_back', true);

        if (isset($config['parameters']['default_front_locale'])) {
            return $config['parameters']['default_front_locale'];
        }

        $config = $this->getConfig('language');

        return $config['default_value'];
    }

    /**
     * Alias of {@link getValues()}.
     *
     * @param $name
     *
     * @return array
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
     *
     * @return mixed
     * @throws \Bigfoot\Bundle\ContextBundle\Exception\NotFoundException
     */
    private function getConfig($name)
    {
        if (is_object($name)) {
            $name = $name->value;
        }
        if (!array_key_exists($name, $this->contexts)) {
            throw new NotFoundException(
                sprintf(
                    'The context %s is undefined. Please add it to the bigfoot_context.contexts configuration in your config.yml file.',
                    $name
                )
            );
        }

        return $this->contexts[$name];
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @return array
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * @param $entity
     *
     * @return mixed
     */
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
     *
     * @return mixed
     */
    function objectToArray($object)
    {
        $array = array();

        if (is_array($object) || is_object($object)) {
            foreach ($object as $key => $value) {
                $array[$key] = (is_array($value) || is_object($value)) ? $this->objectToArray($value) : $value;
            }
        }

        return $array;
    }

    /**
     * @param $entityClass
     *
     * @return mixed
     */
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

    /**
     * @param $entityClass
     * @param $values
     */
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
     * @param \ReflectionClass $reflClass
     *
     * @return array
     */
    public function getContextAnnotations(\ReflectionClass $reflClass)
    {
        $reader      = new AnnotationReader();
        $annotations = $reader->getClassAnnotations($reflClass);

        return array_values(
            array_filter(
                $annotations,
                function ($annotation) {
                    return get_class($annotation) == 'Bigfoot\Bundle\ContextBundle\Annotation\Bigfoot\Context';
                }
            )
        );
    }

    /**
     * @return array
     */
    public function getQueued()
    {
        return $this->queued;
    }

    /**
     * @return array
     */
    public function clearQueue()
    {
        return $this->queued = array();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return self
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }
}
