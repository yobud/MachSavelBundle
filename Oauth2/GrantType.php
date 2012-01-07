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
 * Grant type interface
 *
 * @package MachSavelBundle
 * @subpackage Oauth2
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 * @version 1.0
 */
interface GrantType
{
    /**
     * Get the name of this grant type
     *
     * @return string Name of this grant type
     */
    public function getName();
    
    /**
     * Validate parameters. If validation fails this method will throw an exception.
     *
     * @see \Mach\SavelBundle\Oauth2\OauthException
     * @param array Parameters to be validated
     */
    public function validate(array &$params);
}