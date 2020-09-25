<?php

/*
 *   ____         _          _
 *  |  _ \  __ _ | |_  __ _ | |__    __ _  ___   ___
 *  | |_) |/ _` || __|/ _` || '_ \  / _` |/ __| / _ \
 *  |  __/| (_| || |_| (_| || |_) || (_| |\__ \|  __/
 *  |_|    \__,_| \__|\__,_||_.__/  \__,_||___/ \___|
 *  
 * This file is part of Kristuff\Patabase.
 *
 * (c) Kristuff <contact@kristuff.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version    0.3.0
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase;

/**
 * Class SqlException
 */
class SqlException extends \Exception
{
    /**
     * Initializes the exception with givn parent exception
     *
     * Converts the error code to int in case driver returns it as string.  
     *
     * @access public
     * @param  string       $message
     * @param  int          $code
     * @param  Exception    $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null) {
        
        // some drivers may return $code as string => force int 
        parent::__construct($message, intval($code), $previous);
    }
}