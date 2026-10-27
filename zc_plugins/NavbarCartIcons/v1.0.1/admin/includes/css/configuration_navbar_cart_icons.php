<?php
/**
 * Navbar Cart Icons -- larger type on this plugin's Configuration page.
 *
 * `admin/includes/admin_html_head.php` walks every installed plugin and, for
 * the page being viewed, pulls in from that plugin's `admin/includes/css/`:
 *
 *     <page>.css          linked
 *     <page>_*.css        linked
 *     <page>_*.php        REQUIRED -- executed, and whatever it prints lands
 *                         inside <head>
 *
 * Identical in v1.5.8 through v3.0.0. A plain `configuration.css` would apply
 * to every configuration group in the store; being PHP, this file reads the
 * group id and prints nothing unless the owner is looking at this plugin's
 * group.
 *
 * @package  NavbarCartIcons
 * @license  http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

$nciViewedGroup = isset($_GET['gID']) ? (int)$_GET['gID'] : 0;

if ($nciViewedGroup > 0 && isset($db)) {
    // Looked up by title rather than stored, because the id is assigned by the
    // store when the group is created and differs between installations.
    $nciGroup = $db->Execute(
        "SELECT configuration_group_id
           FROM " . TABLE_CONFIGURATION_GROUP . "
          WHERE configuration_group_title = 'Navbar Cart Icons'
          LIMIT 1"
    );

    if (!$nciGroup->EOF && (int)$nciGroup->fields['configuration_group_id'] === $nciViewedGroup) {
        /* 1.2rem floor. Form controls are listed explicitly: font-size is not
         * inherited into input, select, textarea or button. */
        echo '<style>' . "\n"
            . '.container-fluid{font-size:1.2rem}' . "\n"
            . '.container-fluid input,.container-fluid select,'
            . '.container-fluid textarea,.container-fluid button,'
            . '.container-fluid .btn{font-size:1.2rem;line-height:1.4}' . "\n"
            . '.container-fluid td,.container-fluid th,'
            . '.container-fluid label,.container-fluid p,'
            . '.container-fluid a{font-size:1.2rem}' . "\n"
            . '.container-fluid .configurationDescription{font-size:1.2rem;line-height:1.5}' . "\n"
            . '.container-fluid code,.container-fluid kbd,'
            . '.container-fluid pre,.container-fluid samp{font-size:1.2rem}' . "\n"
            . '</style>' . "\n";
    }
}
