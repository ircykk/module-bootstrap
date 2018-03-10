<?php
/**
 * PrestaShop bootstrap module containing most of features:
 *
 * - Install, uninstall, upgrade
 * - Configure, admin tabs, CRUD
 * - Front & back office hooks
 * - Forms (HelperForm)
 * - Templates
 * - Override classes, controllers and views
 * - and more...
 *
 * PHP version 5.3, PrestaShop 1.6-1.7x
 *
 * LICENSE: MIT https://api.github.com/licenses/mit
 *
 * @category PrestaShop
 * @package  PrestaShop
 * @author   Ireneusz Kierkowski <ircykk@gmail.com>
 * @license  https://api.github.com/licenses/mit  MIT
 * @link     https://github.com/ircykk/module-bootstrap
 * @see      https://github.com/PrestaShop
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function will run if you change module class field
 * version to "1.1.0" or higher and upload module in PS admin or FTP.
 *
 * @param object $module Module instance
 *
 * @return bool
 */
function upgrade_module_1_1_0($module)
{
    /**
     * Your code goes here, update DB etc.
     */

    return true;
}
