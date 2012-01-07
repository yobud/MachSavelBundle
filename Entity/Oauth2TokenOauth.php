<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Oauth2TokenOauth extends Oauth2Token
{
    public function authorize($protected_resource_url, $http_method, array &$params, array &$http_headers)
    {
        $http_headers['Authorization'] = 'OAuth ' . $this->getAccessToken();
    }
}