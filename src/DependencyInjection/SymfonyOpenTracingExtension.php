<?php

declare(strict_types=1);

namespace Adtechpotok\Bundle\SymfonyOpenTracing\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class SymfonyOpenTracingExtension extends ConfigurableExtension
{
    /**
     * Configures the passed container according to the merged configuration.
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resource/config'));
        $loader->load('services.yml');

        $container->setParameter('symfony_open_tracing.service_name', $mergedConfig['service_name']);
        $container->setParameter('symfony_open_tracing.enabled', $mergedConfig['enabled']);
        $container->setParameter('symfony_open_tracing.tracer_config', $mergedConfig['tracer_config']);
    }
}
