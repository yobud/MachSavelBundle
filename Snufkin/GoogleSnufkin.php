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

/**
 * Google Snufkin implementation
 *
 * @see \Mach\SavelBundle\Snufkin
 * @final
 */
final class GoogleSnufkin extends Snufkin
{
    protected function configure()
    {
        $this->set('oauth.authorization.endpoint', 'https://accounts.google.com/o/oauth2/auth');
        $this->set('oauth.access_token.endpoint', 'https://accounts.google.com/o/oauth2/token');
        $this->set('oauth.scopes', array(
            'email'     => 'https://www.googleapis.com/auth/userinfo.email',
            'profile'   => 'https://www.googleapis.com/auth/userinfo.profile'
        ));
        $this->set('oauth.endpoints', array(
            'user-info' => 'https://www.googleapis.com/oauth2/v1/userinfo',
        ));
    }
}