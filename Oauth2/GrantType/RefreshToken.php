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
class RefreshToken implements GrantType
{
    public function getName()
    {
        return 'refresh_token';
    }
    
    public function validate(array &$params)
    {
        if (!isset($params['refresh_token']))
        {
            throw new \Exception('The \'refresh_token\' parameter must be defined for the refresh token grant type');
        }
    }
}