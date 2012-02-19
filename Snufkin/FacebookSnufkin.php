<?php

/**
 * FacebookSnufkin.php
 *
 * @author Jérémy Hubert <jeremy.hubert@infogroom.fr>
 */

namespace Mach\SavelBundle\Snufkin;

/**
 * Facebook Snufkin implementation
 *
 * @see \Mach\SavelBundle\Snufkin
 * @final
 */
final class FacebookSnufkin extends Snufkin
{
    protected function configure()
    {
        $this->set('oauth.authorization.endpoint', 'https://www.facebook.com/dialog/oauth');
        $this->set('oauth.access_token.endpoint', 'https://graph.facebook.com/oauth/access_token');
        $this->set('oauth.scopes', array(
            'email'     => 'email',
            'user_about_me' => 'user_about_me',
        ));
        $this->set('oauth.endpoints', array(
            'user-info' => 'https://graph.facebook.com/me',
        ));
    }
}
