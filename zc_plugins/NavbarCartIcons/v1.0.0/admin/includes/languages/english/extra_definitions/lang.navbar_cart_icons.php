<?php
/**
 * Navbar Cart Icons -- admin language strings (English).
 *
 * BOX_CONFIGURATION_NAVBAR_CART_ICONS is the `language_key` the
 * ScriptedInstaller writes into the `admin_pages` table, so it must exist
 * before the menus are drawn -- hence `extra_definitions`.
 *
 * @package  NavbarCartIcons
 * @license  http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

$define = [
    'BOX_CONFIGURATION_NAVBAR_CART_ICONS' => 'Navbar Cart Icons',
];

return $define;
