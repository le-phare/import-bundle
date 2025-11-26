<?php

namespace LePhare\ImportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ImportLoaderPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public const LOADER_TAG = 'lephare_import.loader';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('lephare.import')) {
            return;
        }

        $definition = $container->getDefinition('lephare.import');

        foreach ($this->findAndSortTaggedServices(self::LOADER_TAG, $container) as $service) {
            $definition->addMethodCall('addLoader', [$service]);
        }
    }
}
