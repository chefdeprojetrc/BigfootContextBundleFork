<?php

namespace Bigfoot\Bundle\ContextBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Bigfoot\Bundle\ContextBundle\DependencyInjection\Compiler\LoaderCompilerPass;
use Bigfoot\Bundle\ContextBundle\DependencyInjection\Compiler\FormTypeCompilerPass;

class BigfootContextBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new LoaderCompilerPass());
    }
}
