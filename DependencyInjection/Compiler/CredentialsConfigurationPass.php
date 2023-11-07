<?php

namespace LePhare\ImportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CredentialsConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('lephare_import.credentials')) {
            return;
        }

        $definition = $container->getDefinition('lephare_import.credentials');

        // We can "steal" the definition from database_connection definition
        $connectionDefinition = $container->getDefinition('doctrine.dbal.default_connection');
        $credentials = $connectionDefinition->getArgument(0);
        $definition->replaceArgument('$url', $credentials['url']);
    }
}
