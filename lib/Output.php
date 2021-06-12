<?php declare(strict_types=1);
 
/** 
 *  ___      _        _
 * | _ \__ _| |_ __ _| |__  __ _ ___ ___
 * |  _/ _` |  _/ _` | '_ \/ _` (_-</ -_)
 * |_| \__,_|\__\__,_|_.__/\__,_/__/\___|
 * 
 * This file is part of Kristuff\Patabase.
 * (c) Kristuff <kristuff@kristuff.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @version    1.0.0
 * @copyright  2017-2021 Kristuff
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