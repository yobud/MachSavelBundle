<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\Oauth2\GrantType;

use Mach\SavelBundle\Oauth2\GrantType;

/**
 * @see \Mach\SavelBundle\GrantType
 */
class AuthorizationCode implements GrantType
{
    public function getName()
    {
        return 'authorization_code';
    }
    
    public function validate(array &$params)
    {
        if (!isset($params['code']))
        {
            throw new \Exception('The \'code\' parameter must be defined for the Authorization Code grant type');
        }
        elseif (!isset($params['redirect_uri']))
        {
            throw new \Exception('The \'redirect_uri\' parameter must be defined for the Authorization Code grant type');
        }
    }
}