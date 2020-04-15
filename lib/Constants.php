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
 * @version    0.2.0
 * @copyright  2017-2020 Kristuff
 */

namespace Kristuff\Patabase;

/**
 * Class Constants
 * 
 * Define api constants 
 */
class Constants
{
    /**
     * Define the constant _PATABASE_COLUMN_LITERALL_ to indicate to the clause WHERE or HAVING in sub queries to refer 
     * to the result of a main query, instead of a non dynamic value 
     */
    const COLUMN_LITERALL = '_PATABASE_COLUMN_LITERALL_';
}