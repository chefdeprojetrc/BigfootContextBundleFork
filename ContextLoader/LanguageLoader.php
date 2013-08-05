<?php

namespace Bigfoot\Bundle\ContextBundle\ContextLoader;

use Bigfoot\Bundle\ContextBundle\Model\AbstractContextLoader;

/**
 * Class LanguageLoader
 * @package Bigfoot\Bundle\ContextBundle\ContextLoader
 */
class LanguageLoader extends AbstractContextLoader
{
    public function getValue()
    {
        if (strpos($this->container->get('request')->getPathInfo(), '/admin') === 0) {
            return $this->getValueForKey($this->container->get('request')->getLocale());
        }
    }

    public function getContextName()
    {
        return 'language';
    }
}