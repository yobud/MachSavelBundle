<?php

/*
 * This file is part of the Savel Bundle for Symfony 2
 *
 * (c) Marcin Chwedziak <marcin@chwedziak.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mach\SavelBundle\Snufkin\Exception;

/**
 * Exception wrapper for "Service Not Found" exception
 *
 * @package MachSavelBundle
 * @subpackage Snufkin
 * @author Marcin Chwedziak <marcin@chwedziak.pl>
 * @version 1.0
 * @see \RuntimeException
 */
class ServiceNotFoundException extends \RuntimeException
{
    /**
     * @param string $message Exception message
     * @param int $code Exception code
     * @param \Exception Previous exception
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string String representation of this exception
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}