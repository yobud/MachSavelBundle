<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class MachSavelExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $this->remapParameters($config, $container, array(
            'default_redirect'   => 'mach_savel.default_redirect',
            'security_provider'  => 'mach_savel.security_provider',
        ));
        
        if (array_key_exists('services', $config))
        {
            $services = array();
            foreach ($config['services'] as $service_config)
            {
                $snufkin = $service_config['snufkin'];
                unset($service_config['snufkin']);
                
                $services[$snufkin] = $service_config;
                $container->setParameter(sprintf('mach_savel.services.%s', str_replace('\\', '.', strtolower($snufkin))), $service_config);
            }
            $container->setParameter('mach_savel.services', $services);
        }
    }
    
    protected function remapParameters(array $config, ContainerBuilder $container, array $map)
    {
        foreach ($map as $name => $paramName)
            if (array_key_exists($name, $config))
                $container->setParameter($paramName, $config[$name]);
    }
}
