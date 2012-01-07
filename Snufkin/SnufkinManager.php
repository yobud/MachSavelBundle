<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\Snufkin;

use Symfony\Component\Routing\RouterInterface;
use Mach\SavelBundle\Snufkin\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Core Snufkin Manager service
 *
 * @package MachSavelBundle
 * @subpackage Snufkin
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 * @version 1.0
 */
class SnufkinManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface Service container
     */
    private $container;
    
    /**
     * @var \Symfony\Component\Routing\RouterInterface Symfony HTTP Router
     */
    private $router;
    
    /**
     * @var array Snufkin instances
     */
    private $snufkins;
    
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface Service container
     * @param \Symfony\Component\Routing\RouterInterface Symfony HTTP Router
     */
    public function __construct(ContainerInterface $container, RouterInterface $router)
    {
        $this->container = $container;
        $this->router = $router;
        $this->snufkins = array();
    }
    
    /**
     * @return string Default redirect URL after successful authentication
     */
    public function getDefaultRedirect()
    {
        return $this->container->getParameter('mach_savel.default_redirect');
    }
    
    /**
     * @return string Default security provider instance id
     */
    public function getSecurityProvider()
    {
        return $this->container->getParameter('mach_savel.security_provider');
    }
    
    /**
     * Configures and returns a proper Snufkin service instance
     *
     * @param string $service Snufkin service name
     * @return \Mach\SavelBundle\Snufkin\Snufkin Snufkin instance
     */
    public function getSnufkin($service)
    {
        if (strpos($service, 'Snufkin') !== strlen($service) - 7)
            $service = 'Mach\SavelBundle\Snufkin\\' . $service . 'Snufkin';
        
        if (isset($this->snufkins[$service]) && $this->snufkins[$service] instanceof Snufkin)
            return $this->snufkins[$service];
        
        try
        {
            $clas = new \ReflectionClass($service);
            
            if (!$clas->isInstantiable() || !$clas->isSubclassOf('Mach\SavelBundle\Snufkin\Snufkin'))
                throw new \LogicException();
            
            $config_name = str_replace('\\', '.', strtolower($service));
            
            $snufkin_config = $this->container->getParameter(sprintf('mach_savel.services.%s', $config_name));
            
            // Get bridge service and connect with the Snufkin
            $bridge_service = $this->container->get($snufkin_config['bridge_service']);
            
            $this->snufkins[$service] = $clas->newInstance($snufkin_config, $bridge_service, $this->router);
            return $this->snufkins[$service];
        }
        catch (\LogicException $ex)
        {
            throw new ServiceNotFoundException(sprintf("Snufkin for \"%s\" service cannot be found", $service));
        }
    }
}