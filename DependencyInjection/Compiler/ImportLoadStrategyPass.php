<?php

namespace LePhare\ImportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImportLoadStrategyPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('lephare_import.load_strategy_repository')) {
            return;
        }

        $definition = $container->getDefinition('lephare_import.load_strategy_repository');

        foreach (array_keys($container->findTaggedServiceIds('import.load_strategy')) as $id) {
            $definition->addMethodCall('addLoadStrategy', [new Reference($id)]);
        }
    }
}
