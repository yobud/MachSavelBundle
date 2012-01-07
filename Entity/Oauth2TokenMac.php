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

class Oauth2TokenMac extends Oauth2Token
{
    public function authorize($protected_resource_url, $http_method, array &$params, array &$http_headers)
    {
        $http_headers['Authorization'] = 'MAC ' . $this->generateMACSignature($protected_resource_url, $params, $http_method);
    }
    
    private function generateMACSignature($url, array $params, $http_method)
    {
        $timestamp = time();
        $nonce = uniqid();
        $query_parameters = array();
        $body_hash = '';
        $parsed_url = parse_url($url);
        
        if (!isset($parsed_url['port']))
        {
            $parsed_url['port'] = ($parsed_url['scheme'] == 'https') ? 443 : 80;
        }

        if ($http_method == 'POST' || $http_method == 'PUT')
        {
            if (!empty($params))
            {
                $body_hash = base64_encode(hash($this->getAccessTokenAlgorithm(), http_build_query($parameters)));
            }
        }
        else
        {
            foreach ($params as $key => $parsed_urlvalue)
            {
                $query_parameters[] = rawurlencode($key) . '=' . rawurlencode($parsed_urlvalue);
            }
            sort($query_parameters);
        }

        $signature = base64_encode(hash_hmac($this->getAccessTokenAlgorithm(), 
            $this->access_token . "\n"
            . $timestamp . "\n" 
            . $nonce . "\n" 
            . $body_hash . "\n"
            . $http_method . "\n" 
            . $parsed_url['host'] . "\n"
            . $parsed_url['port'] . "\n"
            . $parsed_url['path'] . "\n"
            . implode($query_parameters, "\n")
            , $this->getAccessTokenSecret()));

        return 'token="' . $this->getAccessToken() . '", timestamp="' . $timestamp . '", nonce="' . $nonce . '", signature="' . $signature . '"';
    }
    /**
     * @var string $access_token_algorithm
     */
    private $access_token_algorithm;

    /**
     * @var string $access_token_secret
     */
    private $access_token_secret;


    /**
     * Set access_token_algorithm
     *
     * @param string $accessTokenAlgorithm
     */
    public function setAccessTokenAlgorithm($accessTokenAlgorithm)
    {
        $this->access_token_algorithm = $accessTokenAlgorithm;
    }

    /**
     * Get access_token_algorithm
     *
     * @return string 
     */
    public function getAccessTokenAlgorithm()
    {
        return $this->access_token_algorithm;
    }

    /**
     * Set access_token_secret
     *
     * @param string $accessTokenSecret
     */
    public function setAccessTokenSecret($accessTokenSecret)
    {
        $this->access_token_secret = $accessTokenSecret;
    }

    /**
     * Get access_token_secret
     *
     * @return string 
     */
    public function getAccessTokenSecret()
    {
        return $this->access_token_secret;
    }
}