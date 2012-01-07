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
use Mach\SavelBundle\Oauth2\AuthBasicClient;
use Mach\SavelBundle\Oauth2\UriClient;

/**
 * Mach\SavelBundle\Entity\Oauth2Token
 *
 * @package MachSavelBundle
 * @subpackage Entity
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 * @version 1.0
 */
abstract class Oauth2Token
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $service_name
     */
    private $service_name;

    /**
     * @var string $client_id
     */
    private $client_id;

    /**
     * @var string $client_secret
     */
    private $client_secret;

    /**
     * @var string $access_token
     */
    private $access_token;

    /**
     * @var integer $expires_at
     */
    private $expires_at;

    /**
     * @var string $refresh_token
     */
    private $refresh_token;

    /**
     * @var string $scope
     */
    private $scope;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set service_name
     *
     * @param string $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->service_name = $serviceName;
    }

    /**
     * Get service_name
     *
     * @return string 
     */
    public function getServiceName()
    {
        return $this->service_name;
    }

    /**
     * Set client_id
     *
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;
    }

    /**
     * Get client_id
     *
     * @return string 
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set client_secret
     *
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->client_secret = $clientSecret;
    }

    /**
     * Get client_secret
     *
     * @return string 
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }

    /**
     * Set access_token
     *
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->access_token = $accessToken;
    }

    /**
     * Get access_token
     *
     * @return string 
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * Set refresh_token
     *
     * @param string $refreshToken
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refresh_token = $refreshToken;
    }

    /**
     * Get refresh_token
     *
     * @return string 
     */
    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    /**
     * Set scope
     *
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Get scope
     *
     * @return string 
     */
    public function getScope()
    {
        return $this->scope;
    }
    
    /**
     * Set expires_at
     *
     * @param datetime $expiresAt
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expires_at = $expiresAt;
    }

    /**
     * Get expires_at
     *
     * @return datetime 
     */
    public function getExpiresAt()
    {
        return $this->expires_at;
    }
    
    /**
     * This method updates <code>params</code> and/or <code>http_headers</code> 
     * parameters using implemented specification of the current token.
     *
     * @param string $protected_resource_url Destination URL for the Token Verification Service
     * @param string $http_method Used HTTP method within this request
     * @param array $params Parameters (passed as references) of the HTTP request
     * @param array $http_headers Headers (passed as references) of the HTTP request
     * @abstract
     */
    abstract public function authorize($protected_resource_url, $http_method, array &$params, array &$http_headers);
    
    /**
     * Creates a proper instance of OAuth2 UriClient class for this token.
     *
     * @param array Services configuration specified within the application configuration
     * @return \Mach\SavelBundle\Oauth2\UriClient OAuth2 client instance of Uri type
     */
    public function buildUriClient(array $services = array())
    {
        $client = null;
        if (isset($services[$this->getServiceName()]))
        {
            $client_id = $services[$this->getServiceName()]['client_id'];
            $client_secret = $services[$this->getServiceName()]['client_secret'];
            
            $client = new UriClient($client_id, $client_secret);
        }
        return $client;
    }
    
    /**
     * Creates a proper instance of OAuth2 AuthBasicClient class for this token.
     *
     * @param array Services configuration specified within the application configuration
     * @return \Mach\SavelBundle\Oauth2\AuthBasicClient OAuth2 client instance of AuthBasic type
     */
    public function buildAuthBasicClient(array $services = array())
    {
        $client = null;
        if (isset($services[$this->getServiceName()]))
        {
            $client_id = $services[$this->getServiceName()]['client_id'];
            $client_secret = $services[$this->getServiceName()]['client_secret'];
            
            $client = new AuthBasicClient($client_id, $client_secret);
        }
        return $client;
    }
}