<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\Controller;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Mach\SavelBundle\Snufkin\Snufkin as Snufkin;

/**
 * Controller that is used for connecting with a proper Snufkin and
 * to confirm the credentials and create/update the user.
 *
 * @package MachSavelBundle
 * @subpackage Controller
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 * @version 1.0
 */
class SnufkinController extends Controller
{
    /**
     * Action used for connecting with an OAuth service.
     *
     * @param string $service Service name parameter
     * @return \Symfony\Component\HttpFoundation\Response Response object
     */
    public function connectAction($service)
    {
        $snufkin_manager = $this->get('mach_savel.snufkin.manager');
        $snufkin = $snufkin_manager->getSnufkin($service);
        
        $request = $this->get('request');
        $referer = $request->server->get('HTTP_REFERER');
        $request->getSession()->set('mach_savel_referer', $referer);
        
        return $this->redirect($snufkin->connect());
    }
    
    /**
     * Action used for confirming credentials fetched with an OAuth service.
     *
     * @param string $service Service name parameter
     * @return \Symfony\Component\HttpFoundation\Response Response object
     */
    public function confirmAction($service)
    {
        $code = $this->get('request')->get('code');
        
        if (empty($code))
        {
            throw new \InvalidArgumentException("You have to provide \"code\" parameter.");
        }
        
        $snufkin_manager = $this->get('mach_savel.snufkin.manager');
        $snufkin = $snufkin_manager->getSnufkin($service);
        
        $token = $snufkin->confirm($code);
        
        $user = $snufkin->callUserBridge($token);
        
        $authentication_token = new UsernamePasswordToken(
            $user,
            null,
            $snufkin_manager->getSecurityProvider(),
            $user->getRoles()
        );
        
        $this->container->get('security.context')->setToken($authentication_token);
        
        $request = $this->get('request');
        $referer = $request->getSession()->get('mach_savel_referer');
        $request->getSession()->remove('mach_savel_referer');
        
        if (empty($referer))
            $referer = $this->get('router')->generate($snufkin_manager->getDefaultRedirect());
        
        return $this->redirect($referer);
    }
}
