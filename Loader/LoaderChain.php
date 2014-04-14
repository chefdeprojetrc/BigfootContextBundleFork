<?php

namespace Bigfoot\Bundle\ContextBundle\Loader;

class LoaderChain
{
    private $loaders;

    public function __construct()
    {
        $this->loaders = array();
    }

    public function addLoader(LoaderInterface $loader, $id)
    {
        $this->loaders[$id] = $loader;
    }

    public function getLoaders()
    {
        return $this->loaders;
    }
}