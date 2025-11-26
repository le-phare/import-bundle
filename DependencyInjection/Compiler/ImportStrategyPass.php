<?php

namespace LePhare\ImportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImportStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('lephare_import.strategy_repository')) {
            return;
        }

        $definition = $container->getDefinition('lephare_import.strategy_repository');

        foreach (array_keys($container->findTaggedServiceIds('import.strategy')) as $id) {
            $definition->addMethodCall('addStrategy', [new Reference($id)]);
        }
    }
}
