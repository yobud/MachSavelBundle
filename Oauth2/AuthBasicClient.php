<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\Oauth2;

/**
 * @see \Mach\SavelBundle\Client
 */
class AuthBasicClient extends Client
{
    protected function updateAccessTokenParams(array &$params, array &$http_headers)
    {
        $params['client_id'] = $this->getClientId();
        $http_headers['Authorization'] = 'Basic ' . base64_encode($this->getClientId() .  ':' . $this->getClientSecret());
    }
}