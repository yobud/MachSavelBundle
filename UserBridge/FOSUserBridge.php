<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\UserBridge;

use Mach\SavelBundle\Entity\Oauth2Token;
use Mach\SavelBundle\Snufkin\Snufkin;
use Mach\SavelBundle\UserBridge\UserBridge;
use FOS\UserBundle\Entity\UserManager;
use Doctrine\Common\Util\Inflector;

/**
 * Implementation of basic user management for FOSUserBundle
 *
 * @link https://github.com/FriendsOfSymfony/FOSUserBundle FOSUserBundle repository on GitHub
 * @see \Mach\SavelBundle\UserBridge\UserBridge
 */
class FOSUserBridge implements UserBridge
{
    private $user_manager = null;
    private $cached_data = array();
    
    public function __construct(UserManager $user_manager)
    {
        $this->user_manager = $user_manager;
    }
    
    private function findOrCreate(Oauth2Token $token, Snufkin $snufkin)
    {
        $criteria = array();
        
        foreach ($snufkin->getDiscriminators() as $discriminator)
        {
            $endpoint = $discriminator['endpoint'];
            if (!isset($this->cached_data[$endpoint]))
            {
                $this->cached_data[$endpoint] = $snufkin->obtainData($token, $endpoint);
            }
                
            $value = Snufkin::traverse($this->cached_data[$endpoint], $discriminator['path']);
            $criteria[$discriminator['property']] = $value;
        }
        
        $user = $this->user_manager->findUserBy($criteria);
        if ($user == null)
        {
            $clas = '\\' . $snufkin->getUserClass();
            $user = new $clas();
            $user->setPlainPassword(md5(time()));
            $user->setEnabled(true);
            $user->addRole('ROLE_OAUTH_CLIENT');
            $user->addRole('ROLE_USER');
            
            foreach ($snufkin->getCreators() as $creator)
            {
                $callable = 'set' . Inflector::classify($creator['property']);
                if (is_callable(array($user, $callable)))
                {
                    $endpoint = $creator['endpoint'];
                    if (!isset($this->cached_data[$endpoint]))
                    {
                        $this->cached_data[$endpoint] = $snufkin->obtainData($token, $endpoint);
                    }
                    
                    $value = Snufkin::traverse($this->cached_data[$endpoint], $creator['path']);
                    if ($value !== null)
                    {
                        call_user_func(array($user, $callable), $value);
                    }
                }
            }
            $this->user_manager->updateUser($user);
        }
        return $user;
    }
    
    public function call(Oauth2Token $token, Snufkin $snufkin)
    {
        $user = $this->findOrCreate($token, $snufkin);
        
        foreach ($snufkin->getBindings() as $binding)
        {
            $callable = 'set' . Inflector::classify($binding['property']);
            if (is_callable(array($user, $callable)))
            {
                $endpoint = $binding['endpoint'];
                if (!isset($this->cached_data[$endpoint]))
                {
                    $this->cached_data[$endpoint] = $snufkin->obtainData($token, $endpoint);
                }
                
                $value = Snufkin::traverse($this->cached_data[$endpoint], $binding['path']);
                if ($value !== null)
                    call_user_func(array($user, $callable), $value);
            }
        }
        $this->user_manager->updateUser($user);
        
        return $user;
    }
}