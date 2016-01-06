<?php

namespace Bigfoot\Bundle\ContextBundle\Loader;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class AbstractLoader
 * @package Bigfoot\Bundle\ContextBundle\Loader
 */
abstract class AbstractLoader implements LoaderInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $contexts;

    /**
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack = null)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param $contexts
     */
    public function setContexts($contexts)
    {
        $this->contexts = $contexts;
    }

    /**
     * @return array
     */
    public function getContexts()
    {
        return $this->contexts;
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function getValueForKey($key)
    {
        if (array_key_exists($key, $this->contexts[$this->getContextName()]['values'])) {
            return $this->contexts[$this->getContextName()]['values'][$key];
        }

        return false;
    }

    /**
     * @return mixed
     */
    public abstract function getContextName();
}