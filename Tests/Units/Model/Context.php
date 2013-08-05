<?php

namespace Bigfoot\Bundle\ContextBundle\Tests\Units\Model;

use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use atoum\AtoumBundle\Test\Units;

/**
 * Class Context
 * @package Bigfoot\Bundle\ContextBundle\Model
 */
class Context extends Units\Test
{
    public function testGetContext()
    {
        $context = new \Bigfoot\Bundle\ContextBundle\Model\Context($this->getMockContainer());

        $firstReturn = array(
            'label' => 'Bar',
            'value' => 'bar',
        );
        $secondReturn = array(
            'label' => 'Foo',
            'value' => 'foo',
        );
        $thirdReturn = array(
            'label' => 'Default',
            'value' => 'default',
        );

        $this
            ->array($context->getContext('foo_context'))
                ->isEqualTo($firstReturn)
            ->array($context->getContext('foo_context'))
                ->isEqualTo($secondReturn)
            ->array($context->getContext('foo_context'))
                ->isEqualTo($thirdReturn)
            ->exception(function () use ($context) { $context->getContext('foobar_context'); })
                ->isInstanceOf('\Bigfoot\Bundle\ContextBundle\Exception\ContextNotFoundException');
    }

    public function testGetContextValues()
    {
        $context = new \Bigfoot\Bundle\ContextBundle\Model\Context($this->getMockContainer());

        $values = array(
            'foo' => 'Foo',
            'bar' => 'Bar',
            'default' => 'Default',
        );

        $this
            ->array($context->getContextValues('foo_context'))
            ->isEqualTo($values);
    }

    private function getMockContainer()
    {
        $container = new \mock\Symfony\Component\DependencyInjection\Container;
        $this->calling($container)->getParameter = function ($name) {
            return array(
                'foo_context' => array(
                    'loaders' => array(
                        'primary' => 'foo_context_loader',
                        'fallback' => 'bar_context_loader',
                    ),
                    'values' => array(
                        'foo' => array(
                            'label' => 'Foo',
                            'value' => 'foo',
                        ),
                        'bar' => array(
                            'label' => 'Bar',
                            'value' => 'bar',
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

        $fooLoader = new \mock\Bigfoot\Bundle\ContextBundle\Model\AbstractContextLoader($container);
        $this->calling($fooLoader)->getContextName = 'foo_context';
        $this->calling($fooLoader)->getValue[0] = null;
        $this->calling($fooLoader)->getValue[2] = array(
            'label' => 'Foo',
            'value' => 'foo',
        );

        $barLoader = new \mock\Bigfoot\Bundle\ContextBundle\Model\AbstractContextLoader($container);
        $this->calling($barLoader)->getContextName = 'foo_context';
        $this->calling($barLoader)->getValue[0] = null;
        $this->calling($barLoader)->getValue[1] = array(
            'label' => 'Bar',
            'value' => 'bar',
        );

        $this->calling($container)->get = function ($name) use ($fooLoader, $barLoader) {
            if ($name == 'foo_context_loader') {
                return $fooLoader;
            }

            if ($name == 'bar_context_loader') {
                return $barLoader;
            }

            throw new ServiceNotFoundException($name);
        };

        return $container;
    }
}