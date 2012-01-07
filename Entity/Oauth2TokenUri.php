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

class Oauth2TokenUri extends Oauth2Token
{
    /**
     * @var string $access_token_param_name
     */
    private $access_token_param_name;


    /**
     * Set access_token_param_name
     *
     * @param string $accessTokenParamName
     */
    public function setAccessTokenParamName($accessTokenParamName)
    {
        $this->access_token_param_name = $accessTokenParamName;
    }

    /**
     * Get access_token_param_name
     *
     * @return string 
     */
    public function getAccessTokenParamName()
    {
        return $this->access_token_param_name;
    }
    
    public function authorize($protected_resource_url, $http_method, array &$params, array &$http_headers)
    {
        $params[$this->getAccessTokenParamName()] = $this->getAccessToken();
    }
}