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

use Symfony\Component\Routing\RouterInterface;
use Mach\SavelBundle\UserBridge\UserBridge;
use Mach\SavelBundle\Entity\Oauth2Token;
use Mach\SavelBundle\Oauth2\UriClient;
use Mach\SavelBundle\Oauth2\GrantType\AuthorizationCode;
use Mach\SavelBundle\Snufkin\Exception\SnufkinOauthException;

/**
 * Core Snufkin abstract class that implements all the behaviour for the service
 *
 * @package MachSavelBundle
 * @subpackage Snufkin
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 * @version 1.0
 * @abstract
 */
abstract class Snufkin
{
    /**
     * @var \Mach\SavelBundle\UserBridge\UserBridge Bridge service
     */
    private $bridge_service = null;
    
    /**
     * @var \Symfony\Component\Routing\RouterInterface Symfony HTTP Router
     */
    private $router = null;
    
    /**
     * @var array Instance parameters
     */
    private $parameters = array();
    
    /**
     * @var array Snufkin configuration parameters
     */
    private $configuration = array();
    
    /**
     * Connects to the OAuth2 service
     *
     * @param string $callback Callback URL
     * @return string URL to be redirected to
     * @see \Mach\SavelBundle\Oauth2\Client::authenticate()
     */
    public function connect($callback = null)
    {
        if ($callback == null || !is_scalar($callback))
        {
            $callback = $this->get('callback', true);
            $callback = $this->router->generate($callback['route'], $callback['params'], true);
        }
        $this->set('oauth.callback', $callback);
        
        $parsed = parse_url($this->get('oauth.authorization.endpoint'));
        $params = array();
        
        if (isset($parsed['query']))
            parse_str($parsed['query'], $params);
        
        $scopes = $this->get('oauth.scopes', false, array());
        $params_scopes = array();
        
        foreach ($this->get('scope', true) as $scope)
            if (isset($scopes[$scope]))
                $params_scopes[] = $scopes[$scope];
        
        if (isset($params_scopes[0]))
            $params['scope'] = implode(' ', $params_scopes);
            
        $client = new UriClient($this->get('client_id', true), $this->get('client_secret', true));
        return $client->authenticate($this->get('oauth.authorization.endpoint'), $this->get('oauth.callback'), $params);
    }
    
    /**
     * Confirms authenticated user
     *
     * @param string $code Authentication code
     * @param string $callback Callback URL
     * @return \Mach\SavelBundle\Entity\Oauth2Token Authenticated and authorized access token
     * @see \Mach\SavelBundle\Oauth2\Client::getAccessToken()
     */
    public function confirm($code, $callback = null)
    {
        if ($callback == null || !is_scalar($callback))
        {
            $callback = $this->get('callback', true);
            $callback = $this->router->generate($callback['route'], $callback['params'], true);
        }
        $this->set('oauth.callback', $callback);
        
        $params = array(
            'code' => $code,
            'redirect_uri' => $this->get('oauth.callback')
        );
        
        $client = new UriClient($this->get('client_id', true), $this->get('client_secret', true));
        $token = $client->getAccessToken($this->get('oauth.access_token.endpoint'), new AuthorizationCode(), $params);
        
        $token->setServiceName(get_class($this));
        
        return $token;
    }
    
    /**
     * @return string User class to be used with the Bridge service
     */
    public function getUserClass()
    {
        return $this->get('user_class', true);
    }
    
    /**
     * @return array Array of bindings to create the user
     */
    public function getCreators()
    {
        $creators = array();
        
        foreach ($this->get('binding', true) as $binding)
        {
            if ($binding['isCreator'] == true)
            {
                unset($binding['isDiscriminator']);
                unset($binding['isCreator']);
                $creators[] = $binding;
            }
        }
        
        if (count($creators) == 0)
            throw new SnufkinOauthException("You have to specify at least one creator binding in configuration");
        
        return $creators;
    }
    
    /**
     * @return array Array of bindings used to discriminate the user
     */
    public function getDiscriminators()
    {
        $discriminators = array();
        
        foreach ($this->get('binding', true) as $binding)
        {
            if ($binding['isDiscriminator'] == true)
            {
                unset($binding['isDiscriminator']);
                unset($binding['isCreator']);
                $discriminators[] = $binding;
            }
        }
        
        if (count($discriminators) == 0)
            throw new SnufkinOauthException("You have to specify at least one discriminator binding in configuration");
        
        return $discriminators;
    }
    
    /**
     * @return array Array of all the bindings for the user entity
     */
    public function getBindings()
    {
        return $this->get('binding', true);
    }
    
    /**
     * @param \Mach\SavelBundle\Entity\Oauth2Token Access token
     * @param string $endpoint Protected resource end-point
     * @param array $params Additional parameters appended to the end-point URL
     * @return array Result of examining the end-point
     */
    public function obtainData(Oauth2Token $token, $endpoint, array $params = array())
    {
        $endpoints = $this->get('oauth.endpoints', false, array());
        
        if (isset($endpoints[$endpoint]))
            $endpoint = $endpoints[$endpoint];
        
        $client = new UriClient($this->get('client_id', true), $this->get('client_secret', true));
        $response = $client->fetchByGet($token, $endpoint, $params);
        
        if ($response['result'] === false)
            throw new SnufkinOauthException($response['error']);
            
        return $response['result'];
    }
    
    /**
     * Executes the Bridge service's method to do all the job relates to user entity
     *
     * @param \Mach\SavelBundle\Entity\Oauth2Token Access token
     */
    public function callUserBridge(Oauth2Token $token)
    {
        return $this->bridge_service->call($token, $this);
    }
    
    /**
     * Sets a parameter for this Snufkin instance
     *
     * @param string $parameter Name
     * @param string $value Value
     */
    protected function set($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }
    
    /**
     * Gets a parameter for this Snufkin instance or for this Snufkin configuration
     *
     * @param string $parameter Name
     * @param bool $from_configuration Should the parameter be retrieved from the configuration
     * @param mixed $default Default value if parameter not found
     * @return mixed The parameter's value
     */
    protected function get($parameter, $from_configuration = false, $default = null)
    {
        if ($from_configuration)
            return isset($this->configuration[$parameter]) ? $this->configuration[$parameter] : $default;
        else
            return isset($this->parameters[$parameter]) ? $this->parameters[$parameter] : $default;
    }
    
    /**
     * Configures the Snufkin instance.
     *
     * @abstract
     */
    abstract protected function configure();
    
    /**
     * @param array $configuration Configuration for this Snufkin
     * @param \Mach\SavelBundle\UserBridge\UserBridge $bridge_service Bridge service instance
     * @param \Symfony\Component\Routing\RouterInterface $router Symfony HTTP Router instance
     */
    public function __construct(array $configuration = array(), UserBridge $bridge_service, RouterInterface $router)
    {
        $this->bridge_service = $bridge_service;
        $this->router = $router;
        $this->configuration = $configuration;
        $this->configure();
    }
    
    /**
     * Traverse through the data path
     *
     * @param array $json_array Array with the data
     * @param string $path Dot-separated path for the requested data
     * @return mixed The data (if found)
     */
    static public function traverse(array &$json_array, $path)
    {
        if (!is_scalar($path))
            return null;
        
        $path = explode('.', $path, 2);
        
        $level = $path[0];
        $subpath = isset($path[1]) ? $path[1] : '';
        
        if (isset($json_array[$level]))
            return !empty($subpath) ? self::traverse($json_array[$level], $subpath) : $json_array[$level];
        else
            return null;
    }
}