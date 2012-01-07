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
class Password implements GrantType
{
    public function getName()
    {
        return 'password';
    }
    
    public function validate(array &$params)
    {
        if (!isset($params['username']))
        {
            throw new \Exception('The \'username\' parameter must be defined for the Password grant type');
        }
        elseif (!isset($params['password']))
        {
            throw new \Exception('The \'password\' parameter must be defined for the Password grant type');
        }
    }
}