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
        $url = $credentials['url'] ?? null;
        if (!$url && !empty($credentials['host'])) {
            $options = [];
            if (!empty($credentials['server_version'])) {
                $options['server_version'] = $credentials['server_version'];
            }
            if (!empty($credentials['charset'])) {
                $options['charset'] = $credentials['charset'];
            }
            $url = sprintf(
                '%s://%s:%s@%s:%s/%s%s',
                str_replace('pdo_', '', $credentials['driver']),
                $credentials['user'],
                urlencode($credentials['password']),
                $credentials['host'],
                $credentials['port'],
                $credentials['dbname'],
                !empty($options) ? '?'.http_build_query($options) : ''
            );
        }
        $definition->replaceArgument('$url', $url);
    }
}
