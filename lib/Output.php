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
 * @version    0.5.0
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase;

/**
 * Class Constants
 * 
 * Define api output constant constants 
 */
abstract class Output
{
    /**
     * Define the constant JSON for json Output
     */
    const JSON = 'JSON';

    /**
     * Define the constant JSON_PRETTY_PRINT for json pretty print Output
     */
    const JSON_PRETTY_PRINT = 'JSONPP';

    /**
     * Define the constant OBJ for objects Output
     */
    const OBJ = 'OBJ';

    /**
     * Define the constant COLUMN for column Output
     */
    const COLUMN = 'COLUMN';

    /**
     * Define the constant ASSOC for associative array Output
     */
    const ASSOC = 'ASSOC';
}