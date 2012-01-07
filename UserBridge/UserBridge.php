<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\UserBridge;

use Mach\SavelBundle\Entity\Oauth2Token;
use Mach\SavelBundle\Snufkin\Snufkin;

/**
 * @package MachSavelBundle
 * @subpackage UserBridge
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 * @version 1.0
 */
interface UserBridge
{
    /**
     * Executes all the functionality needed to create/update the user entity within its repository
     *
     * @param \Mach\SavelBundle\Entity\Oauth2Token $token Access token
     * @param \Mach\SavelBundle\Snufkin\Snufkin $snufkin Snufkin instance
     */
    public function call(Oauth2Token $token, Snufkin $snufkin);
}