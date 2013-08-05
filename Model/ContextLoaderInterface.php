<?php

namespace Bigfoot\Bundle\ContextBundle\Model;

/**
 * Class ContextLoaderInterface
 * @package Bigfoot\Bundle\ContextBundle\Model
 */
interface ContextLoaderInterface
{
    /**
     * @return mixed
     */
    public function getValue();
}