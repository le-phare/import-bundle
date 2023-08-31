<?php

namespace LePhare\ImportBundle;

use LePhare\ImportBundle\DependencyInjection\Compiler\CredentialsConfigurationPass;
use LePhare\ImportBundle\DependencyInjection\Compiler\ImportLoaderPass;
use LePhare\ImportBundle\DependencyInjection\Compiler\ImportLoadStrategyPass;
use LePhare\ImportBundle\DependencyInjection\Compiler\ImportStrategyPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LePhareImportBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new CredentialsConfigurationPass());
        $container->addCompilerPass(new ImportLoaderPass());
        $container->addCompilerPass(new ImportStrategyPass());
        $container->addCompilerPass(new ImportLoadStrategyPass());
    }
}
