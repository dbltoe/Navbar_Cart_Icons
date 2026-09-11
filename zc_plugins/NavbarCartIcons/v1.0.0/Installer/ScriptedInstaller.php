<?php
/**
 * Navbar Cart Icons -- Plugin Manager installer.
 *
 * Deliberately limited to the API that exists in every supported Zen Cart
 * release (v1.5.8 -> v3.0.0):
 *
 *   - only `executeInstallerSql()` is used for database work. The convenience
 *     helpers (`addConfigurationKey()`, `getOrCreateConfigGroupId()`, ...) were
 *     added in ZC v2.0.1/v2.1.0 and do not exist on v1.5.8.
 *   - `executeUpgrade()` is declared with an optional argument, because ZC
 *     v1.5.8 calls it with none and ZC v2.x/v3.x calls it with $oldVersion.
 *
 * Every step is idempotent, so an upgrade is simply a re-run of the install.
 *
 * @package  NavbarCartIcons
 * @license  http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    /**
     * Title of the configuration group created for this plugin.
     */
    public const CONFIG_GROUP_TITLE = 'Navbar Cart Icons';

    /**
     * admin_pages.page_key values this plugin owns.
     */
    public const ADMIN_PAGE_KEYS = ['configNavbarCartIcons'];

    /**
     * plugin_control.unique_key for this plugin -- its zc_plugins directory.
     */
    public const PLUGIN_KEY = 'NavbarCartIcons';
    public const PLUGIN_BASE_NAME = 'Navbar Cart Icons';
    public const PLUGIN_OFF_SUFFIX = ' - Mod Not Turned On';

    protected function executeInstall()
    {
        $configGroupId = $this->nciGetOrCreateConfigGroup();
        if ($configGroupId === 0) {
            return false;
        }

        $this->nciAddConfigurationKeys($configGroupId);
        $this->nciRegisterAdminPages($configGroupId);
        $this->nciSyncPluginControlName(false);

        // No quotes in the message: zen_record_admin_activity() escapes them
        // on the way in and the log then shows them backslashed.
        $this->nciLog('Navbar Cart Icons: installed/upgraded. The icons show beside the hamburger while Enable Navbar Cart Icons? is set to true and the cart has something in it.', 'warning');

        return true;
    }

    /**
     * ZC v1.5.8 calls this with no argument; ZC v2.x/v3.x passes $oldVersion.
     */
    protected function executeUpgrade($oldVersion = null)
    {
        // The install routine is idempotent, so re-running it brings any older
        // installation up to the current settings without touching values the
        // store owner has already changed.
        return $this->executeInstall();
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages(self::ADMIN_PAGE_KEYS);

        $groupId = $this->nciGetConfigGroupId();
        if ($groupId > 0) {
            $this->executeInstallerSql(
                "DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_group_id = " . $groupId
            );
            $this->executeInstallerSql(
                "DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = " . $groupId
            );
        }

        // Put the plugin's name back before the row stops being ours to fix.
        //
        // On v1.5.8/v2.0/v2.1 the Plugin Manager scan never rewrites
        // plugin_control.name for an existing row, and the admin function that
        // keeps it honest stops loading the moment the plugin is uninstalled.
        // Skipping this would leave "Navbar Cart Icons - Mod Not Turned On"
        // sitting in the Not Installed list permanently.
        $this->nciSyncPluginControlName(true);

        $this->nciLog('Navbar Cart Icons: uninstalled. Settings removed; no template file was ever changed, so there is nothing else to undo.', 'warning');

        return true;
    }

    /**
     * Write a line to the admin activity log, if we can.
     *
     * Guarded because the installer runs in an unusual context -- Plugin
     * Manager, mid-transaction, sometimes before this plugin's own files are
     * loaded -- and a missing logger must never be the thing that fails an
     * install or, worse, an uninstall.
     *
     * @param string $message
     * @param string $severity
     * @return void
     */
    protected function nciLog($message, $severity = 'info')
    {
        if (function_exists('zen_record_admin_activity')) {
            zen_record_admin_activity($message, $severity);
        }
    }

    /**
     * Write the plugin_control name the current state calls for.
     *
     * @param bool $clean True to force the plain name, used on uninstall.
     * @return bool
     */
    protected function nciSyncPluginControlName($clean = false)
    {
        if (!defined('TABLE_PLUGIN_CONTROL')) {
            return true;
        }

        $name = self::PLUGIN_BASE_NAME;
        if (!$clean) {
            // Read straight from the table: on install the constant either does
            // not exist yet or still holds the value from before this run.
            $status = $this->nciGetConfigValue('NAVBAR_CART_ICONS_STATUS', 'true');
            if ($status !== 'true') {
                $name .= self::PLUGIN_OFF_SUFFIX;
            }
        }

        return $this->executeInstallerSql(
            "UPDATE " . TABLE_PLUGIN_CONTROL . "
                SET name = '" . $this->dbConn->prepare_input($name) . "'
              WHERE unique_key = '" . $this->dbConn->prepare_input(self::PLUGIN_KEY) . "'
              LIMIT 1"
        );
    }

    /* ----------------------------------------------------------------- *
     * Helpers
     * ----------------------------------------------------------------- */

    protected function nciGetConfigGroupId()
    {
        $sql =
            "SELECT configuration_group_id
               FROM " . TABLE_CONFIGURATION_GROUP . "
              WHERE configuration_group_title = '" . $this->dbConn->prepare_input(self::CONFIG_GROUP_TITLE) . "'
              LIMIT 1";
        $check = $this->dbConn->Execute($sql);

        return ($check->EOF) ? 0 : (int)$check->fields['configuration_group_id'];
    }

    protected function nciGetOrCreateConfigGroup()
    {
        $groupId = $this->nciGetConfigGroupId();
        if ($groupId > 0) {
            return $groupId;
        }

        $created = $this->executeInstallerSql(
            "INSERT INTO " . TABLE_CONFIGURATION_GROUP . "
                (configuration_group_title, configuration_group_description, sort_order, visible)
             VALUES
                ('" . $this->dbConn->prepare_input(self::CONFIG_GROUP_TITLE) . "',
                 'Shows Shopping Cart and Checkout icons beside the hamburger button of a ZCA Bootstrap navbar on phones and tablets, without changing any template file.',
                 0, 1)"
        );
        if ($created === false) {
            return 0;
        }

        $groupId = $this->nciGetConfigGroupId();
        if ($groupId > 0) {
            // Zen Cart convention: a configuration group's sort_order matches its id.
            $this->executeInstallerSql(
                "UPDATE " . TABLE_CONFIGURATION_GROUP . "
                    SET sort_order = $groupId
                  WHERE configuration_group_id = $groupId
                  LIMIT 1"
            );
        }

        return $groupId;
    }

    protected function nciGetConfigValue($key, $default = '')
    {
        $sql =
            "SELECT configuration_value
               FROM " . TABLE_CONFIGURATION . "
              WHERE configuration_key = '" . $this->dbConn->prepare_input($key) . "'
              LIMIT 1";
        $check = $this->dbConn->Execute($sql);

        return ($check->EOF) ? $default : $check->fields['configuration_value'];
    }

    protected function nciRegisterAdminPages($configGroupId)
    {
        // zen_register_admin_page() performs a plain INSERT, so clear first.
        zen_deregister_admin_pages(self::ADMIN_PAGE_KEYS);

        zen_register_admin_page(
            'configNavbarCartIcons',
            'BOX_CONFIGURATION_NAVBAR_CART_ICONS',
            'FILENAME_CONFIGURATION',
            'gID=' . $configGroupId,
            'configuration',
            'Y',
            $configGroupId
        );
    }

    /**
     * A SQL literal for a nullable string column.
     *
     * @param string $value
     * @return string
     */
    protected function nciSqlOrNull($value)
    {
        if ($value === '' || $value === null) {
            return 'NULL';
        }

        return "'" . $this->dbConn->prepare_input($value) . "'";
    }

    /**
     * Insert every configuration key. INSERT IGNORE keeps this idempotent --
     * `configuration_key` carries a UNIQUE index in every supported release, so
     * settings the store owner has already changed are never overwritten.
     */
    protected function nciAddConfigurationKeys($configGroupId)
    {
        foreach ($this->nciConfigurationKeys() as $key) {
            $sql =
                "INSERT IGNORE INTO " . TABLE_CONFIGURATION . "
                    (configuration_title, configuration_key, configuration_value, configuration_description,
                     configuration_group_id, sort_order, date_added, use_function, set_function, val_function)
                 VALUES
                    ('" . $this->dbConn->prepare_input($key['title']) . "',
                     '" . $this->dbConn->prepare_input($key['key']) . "',
                     '" . $this->dbConn->prepare_input($key['value']) . "',
                     '" . $this->dbConn->prepare_input($key['description']) . "',
                     " . (int)$configGroupId . ",
                     " . (int)$key['sort_order'] . ",
                     now(),
                     NULL,
                     " . $this->nciSqlOrNull($key['set_function']) . ",
                     NULL)";

            if ($this->executeInstallerSql($sql) === false) {
                return false;
            }

            // INSERT IGNORE deliberately leaves an existing row alone, which is
            // right for the VALUE -- that belongs to the store owner and must
            // never be reset by an upgrade. It is wrong for everything else:
            // the label, the help text, the ordering and the input type all
            // belong to the plugin, and a store upgrading from an earlier
            // version should get the current wording rather than keep the
            // old. So follow the insert with an update of the metadata only.
            // On a fresh install this rewrites what was just inserted, which
            // is harmless.
            $update =
                "UPDATE " . TABLE_CONFIGURATION . "
                    SET configuration_title = '" . $this->dbConn->prepare_input($key['title']) . "',
                        configuration_description = '" . $this->dbConn->prepare_input($key['description']) . "',
                        configuration_group_id = " . (int)$configGroupId . ",
                        sort_order = " . (int)$key['sort_order'] . ",
                        set_function = " . $this->nciSqlOrNull($key['set_function']) . ",
                        last_modified = now()
                  WHERE configuration_key = '" . $this->dbConn->prepare_input($key['key']) . "'
                  LIMIT 1";

            if ($this->executeInstallerSql($update) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * The full list of configuration records this plugin owns.
     *
     * Titles are Title Case and end in a colon, or a question mark where the
     * setting is a yes/no question.
     */
    protected function nciConfigurationKeys()
    {
        $boolean = "zen_cfg_select_option(array('true', 'false'), ";

        return [
            [
                'key' => 'NAVBAR_CART_ICONS_STATUS',
                'title' => 'Enable Navbar Cart Icons?',
                // On from the start, on purpose: with every other setting at
                // its default this shows the two icons whenever the cart has
                // something in it, which is exactly what the plugin is for.
                'value' => 'true',
                'description' => 'Master switch. <strong>true</strong> shows the icons beside the hamburger on every storefront page where the cart has something in it; <strong>false</strong> hides them again without uninstalling.',
                'sort_order' => 10,
                'set_function' => $boolean,
            ],

            /* ---- what is shown ---- */
            [
                'key' => 'NAVBAR_CART_ICONS_SHOW_CART',
                'title' => 'Show The Shopping Cart Icon?',
                'value' => 'true',
                'description' => 'The first icon, linking to the shopping cart page, the same page the menu\'s <em>Shopping Cart</em> entry goes to.',
                'sort_order' => 20,
                'set_function' => $boolean,
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_SHOW_CHECKOUT',
                'title' => 'Show The Checkout Icon?',
                'value' => 'true',
                'description' => 'The second icon, linking straight into checkout, the same page the menu\'s <em>Checkout</em> entry goes to. Only ever shown while the cart has something in it.',
                'sort_order' => 30,
                'set_function' => $boolean,
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_WHEN_EMPTY',
                'title' => 'Show The Cart Icon When The Cart Is Empty?',
                'value' => 'false',
                'description' => '<strong>false</strong> matches ZCA Bootstrap\'s own menu, which lists Shopping Cart and Checkout only once there is something in the cart, so a visitor who hasn\'t added anything sees nothing extra. <strong>true</strong> shows the cart icon on every page regardless; the checkout icon still waits for an item.',
                'sort_order' => 40,
                'set_function' => $boolean,
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_COUNT_BADGE',
                'title' => 'Show The Item Count On The Cart Icon?',
                'value' => 'false',
                'description' => '<strong>true</strong> adds a small badge to the cart icon with the number of items in the cart, as the cart itself counts them.',
                'sort_order' => 50,
                'set_function' => $boolean,
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_CART_CLASS',
                'title' => 'Shopping Cart Icon Class:',
                'value' => 'fas fa-shopping-cart',
                'description' => 'The Font Awesome classes for the cart icon. ZCA Bootstrap loads Font Awesome on every page, so any of its icons works: <code>fas fa-shopping-cart</code>, <code>fas fa-shopping-basket</code>, <code>fas fa-shopping-bag</code>. Letters, digits, spaces and hyphens only.',
                'sort_order' => 60,
                'set_function' => '',
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_CHECKOUT_CLASS',
                'title' => 'Checkout Icon Class:',
                'value' => 'fas fa-credit-card',
                'description' => 'The Font Awesome classes for the checkout icon: <code>fas fa-credit-card</code>, <code>fas fa-cash-register</code>, <code>fas fa-money-check</code>. Letters, digits, spaces and hyphens only.',
                'sort_order' => 70,
                'set_function' => '',
            ],

            /* ---- how it looks ---- */
            [
                'key' => 'NAVBAR_CART_ICONS_SHRINK_BRAND',
                'title' => 'Keep Everything On One Line?',
                'value' => 'true',
                'description' => 'A store name, logo or trademark ahead of the icons has a fixed width, and on a narrow phone, or with a large-text setting, it can push the icons and the hamburger down onto a second line. <strong>true</strong> lets that first item shrink and clip with an ellipsis instead, so the icons and the hamburger always stay in the top row. <strong>false</strong> leaves the navbar to wrap as it likes.',
                'sort_order' => 100,
                'set_function' => $boolean,
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_LOAD_CSS',
                'title' => 'Load The Plugin Stylesheet?',
                'value' => 'true',
                'description' => '<strong>true</strong> links the plugin\'s small stylesheet and applies the settings below. Set to <strong>false</strong> if you would rather style <code>#navbarCartIcons</code> entirely from your own template CSS; nothing of the plugin\'s then reaches the page but the icons themselves.',
                'sort_order' => 110,
                'set_function' => $boolean,
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_COLOR',
                'title' => 'Icon Color:',
                'value' => '',
                'description' => 'Any CSS color, for example <code>#9a044f</code> or <code>white</code>. Leave it <strong>empty</strong> to use the same color as the navbar\'s other links. A value that does not look like a color is ignored.',
                'sort_order' => 120,
                'set_function' => '',
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_COLOR_HOVER',
                'title' => 'Icon Hover Color:',
                'value' => '',
                'description' => 'The color while a pointer is over an icon or it has keyboard focus. Leave it <strong>empty</strong> to use the navbar\'s own hover color.',
                'sort_order' => 130,
                'set_function' => '',
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_SIZE',
                'title' => 'Icon Size:',
                'value' => '1.5rem',
                'description' => 'Any CSS size, for example <code>1.5rem</code>, <code>1.25em</code> or <code>24px</code>. The touch target around each icon stays at least 44px square whatever the size.',
                'sort_order' => 140,
                'set_function' => '',
            ],
            [
                'key' => 'NAVBAR_CART_ICONS_GAP',
                'title' => 'Space Between The Icons:',
                'value' => '10px',
                'description' => 'The gap between the two icons, and between the last icon and the hamburger. Any CSS length; <code>10px</code> is enough finger room to keep taps from landing on the neighbor.',
                'sort_order' => 150,
                'set_function' => '',
            ],
        ];
    }
}
