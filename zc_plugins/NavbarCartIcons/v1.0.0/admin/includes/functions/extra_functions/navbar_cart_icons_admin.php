<?php
/**
 * Navbar Cart Icons -- keeps the Plugin Manager row telling the truth.
 *
 * `manifest.php` appends " - Mod Not Turned On" to the plugin name while
 * NAVBAR_CART_ICONS_STATUS is not 'true'. That only works if the stored row
 * is refreshed from the manifest, and it is not refreshed everywhere:
 *
 *   v2.2, v2.3, v3.0   PluginManager::updatePluginControl() calls
 *                      upsertMany(), which refreshes name and description on
 *                      every scan.
 *   v1.5.8, v2.0, v2.1 the same method calls Eloquent's
 *                      upsert($values, ['id'], ['infs']) -- only `infs` is
 *                      rewritten. `name` is written by the INSERT that first
 *                      created the row and never again.
 *
 * On those three releases the notice would therefore be permanent. Zen Cart
 * loads every installed plugin's `admin/includes/functions/extra_functions/`
 * on every admin page (through v2.3; v3.0.0 refreshes the name itself), so
 * this file compares the stored name with what the current setting implies
 * and corrects it when they differ.
 *
 * Cost: one indexed SELECT on the Plugin Manager page only, and an UPDATE just
 * on the visit after the setting changes. Nothing on any other page.
 *
 * @package  NavbarCartIcons
 * @license  http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG') || IS_ADMIN_FLAG !== true) {
    die('Illegal Access');
}

/**
 * The plugin_control.unique_key this plugin owns -- its directory name under
 * zc_plugins/.
 */
define('NAVBAR_CART_ICONS_PLUGIN_KEY', 'NavbarCartIcons');

/**
 * The name with no notice appended. Must match manifest.php exactly.
 */
define('NAVBAR_CART_ICONS_PLUGIN_BASE_NAME', 'Navbar Cart Icons');

/**
 * Appended while the plugin is installed but switched off. Plain text: the
 * column is varchar(64) and echoed unescaped.
 */
define('NAVBAR_CART_ICONS_PLUGIN_OFF_SUFFIX', ' - Mod Not Turned On');

/**
 * Correct plugin_control.name if it disagrees with the current setting.
 *
 * @return void
 */
function nci_admin_sync_plugin_name()
{
    global $db;

    // Only the Plugin Manager displays this column, so only that page needs to
    // pay for the check.
    $page = isset($_GET['cmd']) ? (string)$_GET['cmd'] : '';
    if ($page !== 'plugin_manager') {
        return;
    }

    if (!defined('TABLE_PLUGIN_CONTROL') || !defined('NAVBAR_CART_ICONS_STATUS')) {
        return;
    }

    $expected = NAVBAR_CART_ICONS_PLUGIN_BASE_NAME;
    if (NAVBAR_CART_ICONS_STATUS !== 'true') {
        $expected .= NAVBAR_CART_ICONS_PLUGIN_OFF_SUFFIX;
    }

    $result = $db->Execute(
        "SELECT name FROM " . TABLE_PLUGIN_CONTROL . "
          WHERE unique_key = '" . zen_db_input(NAVBAR_CART_ICONS_PLUGIN_KEY) . "'
          LIMIT 1"
    );

    // No row yet: Plugin Manager has not scanned since the files were uploaded.
    // Its own INSERT will carry the right name, so there is nothing to correct.
    if ($result->EOF || $result->fields['name'] === $expected) {
        return;
    }

    $db->Execute(
        "UPDATE " . TABLE_PLUGIN_CONTROL . "
            SET name = '" . zen_db_input($expected) . "'
          WHERE unique_key = '" . zen_db_input(NAVBAR_CART_ICONS_PLUGIN_KEY) . "'
          LIMIT 1"
    );
}

// The Plugin Manager builds its table after all of this has loaded, so
// correcting the row here means the page renders the corrected value on the
// same request rather than one refresh later.
nci_admin_sync_plugin_name();
