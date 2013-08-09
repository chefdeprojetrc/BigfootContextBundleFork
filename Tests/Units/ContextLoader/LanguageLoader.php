<?php

namespace Bigfoot\Bundle\ContextBundle\Tests\Units\ContextLoader;

use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use atoum\AtoumBundle\Test\Units;

/**
 * Class Context
 * @package Bigfoot\Bundle\ContextBundle\ContextLoader
 */
class LanguageLoader extends Units\Test
{
    public function testGetValue()
    {
        $loader = new \Bigfoot\Bundle\ContextBundle\ContextLoader\LanguageLoader($this->getMockContainer());

        $secondReturn = array(
            'label' => 'English',
            'value' => 'en',
        );
        $thirdReturn = array(
            'label' => 'French',
            'value' => 'fr',
        );

        $this
            ->variable($loader->getValue())
                ->isNull()
            ->array($loader->getValue())
                ->isEqualTo($secondReturn)
            ->array($loader->getValue())
                ->isEqualTo($thirdReturn);
    }

    private function getMockContainer()
    {
        $container = new \mock\Symfony\Component\DependencyInjection\Container;
        $this->calling($container)->getParameter = function ($name) {
            return array(
                'language' => array(
                    'loaders' => array(
                        'primary' => 'language_loader',
                    ),
                    'values' => array(
                        'fr' => array(
                            'label' => 'French',
                            'value' => 'fr',
                        ),
                        'en' => array(
                            'label' => 'English',
                            'value' => 'en',
                        ),
                        'default' => array(
                            'label' => 'Default',
                            'value' => 'default',
                        ),
                    ),
                    'default_value' => 'default',
                ),
            );
        };

        $request = new \mock\Symfony\Component\HttpFoundation\Request();
        $this->calling($request)->getPathInfo[0] = '/admin';
        $this->calling($request)->getPathInfo[1] = '/foo/bar';
        $this->calling($request)->getLocale[0] = 'en';
        $this->calling($request)->getLocale[2] = 'fr';

        $this->calling($container)->get = function () use ($request) {
            return $request;
        };

        return $container;
    }
}
