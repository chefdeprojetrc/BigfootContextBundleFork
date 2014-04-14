<?php

namespace Bigfoot\Bundle\ContextBundle\Loader;

use Symfony\Component\HttpFoundation\Request;

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
     * @var Array
     */
    protected $contexts;

    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function setContexts($contexts)
    {
        $this->contexts = $contexts;
    }

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
        $contextConfiguration = $this->contexts;

        if (array_key_exists($key, $this->contexts[$this->getContextName()]['values'])) {
            return $this->contexts[$this->getContextName()]['values'][$key];
        }
    }

    /**
     * @return mixed
     */
    public abstract function getContextName();
}