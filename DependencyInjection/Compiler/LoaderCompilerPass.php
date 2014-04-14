<?php

namespace Bigfoot\Bundle\ContextBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class LoaderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('bigfoot_context.chain.loader')) {
            return;
        }

        $definition     = $container->getDefinition('bigfoot_context.chain.loader');
        $taggedServices = $container->findTaggedServiceIds('bigfoot_context.loader');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addLoader',
                array(new Reference($id), $id)
            );
        }
    }
}