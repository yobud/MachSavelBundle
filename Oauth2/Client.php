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

use Mach\SavelBundle\Entity\Oauth2Token;
use Mach\SavelBundle\Entity\Oauth2TokenBearer;
use Mach\SavelBundle\Entity\Oauth2TokenMac;
use Mach\SavelBundle\Entity\Oauth2TokenOauth;
use Mach\SavelBundle\Entity\Oauth2TokenUri;

/**
 * Implementation of OAuth2 Client for executing all operations within 
 * the OAuth2 protocole.
 *
 * @package MachSavelBundle
 * @subpackage Oauth2
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 * @version 1.0
 * @abstract
 */
abstract class Client
{
    
    /**
     * Client ID for the OAuth2 service
     * @var string
     */
    private $client_id;
    
    /**
     * Client Secret for the OAuth2 service
     * @var string
     */
    private $client_secret;
    
    /**
     * The name of a file holding one or more certificates to verify the peer with
     * @var string
     */
    private $certificate_file;
    
    /**
     * @param string $client_id Client ID for the OAuth2 service
     * @param string $client_secret Client Secret for the OAuth2 service
     * @param string $certificate_file The name of a file holding one or more certificates to verify the peer with
     */
    public function __construct($client_id, $client_secret, $certificate_file = null)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->certificate_file = $certificate_file;
    }
    
    /**
     * Get Client ID bound with this instance
     *
     * @return string Client ID
     */
    public function getClientId()
    {
        return $this->client_id;
    }
    
    /**
     * Get Client Secret bound with this instance
     *
     * @return string Client Secret
     */
    public function getClientSecret()
    {
        return $this->client_secret;
    }
    
    /**
     * Authenticates the application within the provider's account
     *
     * @param string $auth_endpoint Authentication end-point
     * @param string $redirect_uri URI that will be used to redirect to after a successful authentication
     * @param array $extra_params Extra parameters that will be appended to the HTTP request
     * @return string URL to be redirected to for authentication purposes
     */
    public function authenticate($auth_endpoint, $redirect_uri, array $extra_params = array())
    {
        $params = array_merge(array(
            'response_type' => 'code',
            'client_id'     => $this->getClientId(),
            'redirect_uri'  => $redirect_uri
        ), $extra_params);
        
        return $auth_endpoint . '?' . http_build_query($params, null, '&');
    }
    
    /**
     * Using authentication code, return the Access Token
     *
     * @param string $token_endpoint Token access end-point
     * @param \Mach\SavelBundle\Oauth2\GrantType Token grant type
     * @param array Parameters to be included with the HTTP request
     * @return \Mach\SavelBundle\Entity\Oauth2Token Instance of a concrete subtype of the token
     */
    public function getAccessToken($token_endpoint, GrantType $grant_type, array $params)
    {
        $params['grant_type'] = $grant_type->getName();
        
        $http_headers = array();
        $this->updateAccessTokenParams($params, $http_headers);
        
        $response = $this->executePostRequest($token_endpoint, $params, $http_headers);
        
        if ($response['result'] === false)
        {
            throw new OauthException($response['error']);
        }

        $info = $response['result'];
        
        $token = null;
        switch ($info['token_type'])
        {
            case 'Bearer':
                $token = new Oauth2TokenBearer();
                break;
            case 'MAC':
                $token = new Oauth2TokenMac();
                $token->setAccessTokenAlgorithm(null);
                $token->setAccessTokenSecret(null);
                break;
            case 'OAuth':
                $token = new Oauth2TokenOauth();
                break;
            case 'Uri':
                $token = new Oauth2TokenUri();
                $token->setAccessTokenParamName('access_token');
                break;
            default:
                throw new OauthException("Invalid token_type returned.");
        }
        
        $token->setAccessToken($info['access_token']);
        
        if (isset($info['refresh_token']))
            $token->setRefreshToken($info['refresh_token']);
        
        if (isset($info['expires_in']))
            $token->setExpiresAt(new \DateTime('@' . (time()+(int)$info['expires_in'])));
        
        if (isset($info['scope']))
            $token->setScope($info['scope']);
            
        return $token;
    }
    
    /**
     * Prepares Token Authorization (using token's methods) and sends a GET request 
     * to protected resource with ana objective to return the data.
     *
     * @param \Mach\SavelBundle\Entity\Oauth2Token $token Token to be used within the query
     * @param string $protected_resource_url URL to the protected resource
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @return array Result of sent HTTP query
     */
    public function fetchByGet(Oauth2Token $token, $protected_resource_url, array $params = array(), array $http_headers = array())
    {
        $token->authorize($protected_resource_url, 'GET', $params, $http_headers);
        return $this->executeGetRequest($protected_resource_url, $params, $http_headers);
    }
    
    /**
     * Prepares Token Authorization (using token's methods) and sends a POST request 
     * to protected resource with ana objective to return the data.
     *
     * @param \Mach\SavelBundle\Entity\Oauth2Token $token Token to be used within the query
     * @param string $protected_resource_url URL to the protected resource
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @return array Result of sent HTTP query
     */
    public function fetchByPost(Oauth2Token $token, $protected_resource_url, array $params = array(), array $http_headers = array())
    {
        $token->authorize($protected_resource_url, 'POST', $params, $http_headers);
        return $this->executePostRequest($protected_resource_url, $params, $http_headers);
    }
    
    /**
     * Prepares Token Authorization (using token's methods) and sends a PUT request 
     * to protected resource with ana objective to return the data.
     *
     * @param \Mach\SavelBundle\Entity\Oauth2Token $token Token to be used within the query
     * @param string $protected_resource_url URL to the protected resource
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @return array Result of sent HTTP query
     */
    public function fetchByPut(Oauth2Token $token, $protected_resource_url, array $params = array(), array $http_headers = array())
    {
        $token->authorize($protected_resource_url, 'PUT', $params, $http_headers);
        return $this->executePutRequest($protected_resource_url, $params, $http_headers, array(), false);
    }
    
    /**
     * Prepares Token Authorization (using token's methods) and sends a DELETE request 
     * to protected resource with ana objective to return the data.
     *
     * @param \Mach\SavelBundle\Entity\Oauth2Token $token Token to be used within the query
     * @param string $protected_resource_url URL to the protected resource
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @return array Result of sent HTTP query
     */
    public function fetchByDelete(Oauth2Token $token, $protected_resource_url, array $params = array(), array $http_headers = array())
    {
        $token->authorize($protected_resource_url, 'DELETE', $params, $http_headers);
        return $this->executeDeleteRequest($protected_resource_url, $params, $http_headers);
    }
    
    /**
     * Prepares Token Authorization (using token's methods) and sends a HEAD request 
     * to protected resource with ana objective to return the data.
     *
     * @param \Mach\SavelBundle\Entity\Oauth2Token $token Token to be used within the query
     * @param string $protected_resource_url URL to the protected resource
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @return array Result of sent HTTP query
     */
    public function fetchByHead(Oauth2Token $token, $protected_resource_url, array $params = array(), array $http_headers = array())
    {
        $token->authorize($protected_resource_url, 'HEAD', $params, $http_headers);
        return $this->executeHeadRequest($protected_resource_url, $params, $http_headers);
    }
    
    /**
     * Updates access token parameters and HTTP headers.
     *
     * @param array $params Parameters
     * @param array $http_headers HTTP headers
     * @abstract
     */
    abstract protected function updateAccessTokenParams(array &$params, array &$http_headers);
    
    /**
     * Sends a PUT request to <code>$url</code>
     *
     * @param string $url Destination URL
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @param array $curl_options Options appended to the CURL request
     * @param bool $application_content_type TRUE if the request should be sent as application/x-www-form-urlencoded
     * @return array Result of sent HTTP query
     */
    private function executePutRequest($url, array $params = array(), array $http_headers = array(), array $curl_options = array(), $application_content_type = true)
    {
        if ($application_content_type)
        {
            $curl_options[CURLOPT_POSTFIELDS] = http_build_query($params);
        }
        
        return $this->callRequest($url, $http_headers, $curl_options);
    }
    
    /**
     * Sends a POST request to <code>$url</code>
     *
     * @param string $url Destination URL
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @param array $curl_options Options appended to the CURL request
     * @param bool $application_content_type TRUE if the request should be sent as application/x-www-form-urlencoded
     * @return array Result of sent HTTP query
     */
    private function executePostRequest($url, array $params = array(), array $http_headers = array(), array $curl_options = array(), $application_content_type = true)
    {
        $curl_options = array_merge(array(
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
        ), $curl_options);
        
        return $this->executePutRequest($url, $params, $http_headers, $curl_options, $application_content_type);
    }
    
    /**
     * Sends a GET request to <code>$url</code>
     *
     * @param string $url Destination URL
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @param array $curl_options Options appended to the CURL request
     * @return array Result of sent HTTP query
     */
    private function executeGetRequest($url, array $params = array(), array $http_headers = array(), array $curl_options = array())
    {
        $url = $url . '?' . http_build_query($params, null, '&');
        
        return $this->callRequest($url, $http_headers, $curl_options);
    }
    
    /**
     * Sends a DELETE request to <code>$url</code>
     *
     * @param string $url Destination URL
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @param array $curl_options Options appended to the CURL request
     * @return array Result of sent HTTP query
     */
    private function executeDeleteRequest($url, array $params = array(), array $http_headers = array(), array $curl_options = array())
    {
        return $this->executeGetRequest($url, $params, $http_headers, $curl_options);
    }
    
    /**
     * Sends a HEAD request to <code>$url</code>
     *
     * @param string $url Destination URL
     * @param array $params Parameters to be included with the HTTP query
     * @param array $http_headers HTTP headers to be used within the query
     * @param array $curl_options Options appended to the CURL request
     * @return array Result of sent HTTP query
     */
    private function executeHeadRequest($url, array $params = array(), array $http_headers = array(), array $curl_options = array())
    {
        $curl_options[CURLOPT_NOBODY] = true;
        return $this->executeDeleteRequest($url, $params, $http_headers, $curl_options);
    }
    
    /**
     * Sends a request to <code>$url</code>
     *
     * @param string $url Destination URL
     * @param array $http_headers HTTP headers to be used within the query
     * @param array $curl_options Options appended to the CURL request
     * @return array Result of sent HTTP query
     */
    private function callRequest($url, array $http_headers, array $curl_options)
    {
        $curl_options[CURLOPT_URL] = $url;
        
        if (count($http_headers) > 0)
        {
            $header = $http_headers;
            array_walk($header, function(&$i, $k){ $i = $k .': ' . $i; });
            $curl_options[CURLOPT_HTTPHEADER] = $header;
        }
        
        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);
        
        if (!empty($this->certificate_file))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $this->certificate_file);
        }
        else
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        
        if (($curl_error = curl_error($ch)) !== '')
        {
            return array(
                'result' => false, 
                'error' => $curl_error
            );
        }
        
        $json_decode = json_decode($result, true);
        curl_close($ch);
        
        return array(
            'result'        => (null === $json_decode) ? $result : $json_decode,
            'code'          => $http_code,
            'content_type'  => $content_type
        );
    }
}