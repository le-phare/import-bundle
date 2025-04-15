<?php

namespace LePhare\ImportBundle\DependencyInjection;

use LePhare\Import\Load\LoaderInterface;
use LePhare\Import\LoadStrategy\LoadStrategyInterface;
use LePhare\ImportBundle\DependencyInjection\Compiler\ImportLoaderPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Mailer\Mailer;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LePhareImportExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $parameters = $container->getParameterBag()->all();

        $container
            ->getDefinition('lephare_import.configuration')
            ->replaceArgument(0, $parameters)
        ;

        $container->setParameter('lephare_import.email_report.recipients', $config['email_report']['recipients']);

        if (!class_exists(Mailer::class)) {
            $container->removeDefinition('lephare_import.email_report_subscriber');
        }

        $container
            ->registerForAutoconfiguration(LoadStrategyInterface::class)
            ->addTag('import.load_strategy')
        ;

        $container
            ->registerForAutoconfiguration(LoaderInterface::class)
            ->addTag(ImportLoaderPass::LOADER_TAG)
        ;
    }
}
