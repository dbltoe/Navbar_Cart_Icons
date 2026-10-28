<?php
/**
 * Navbar Cart Icons -- plugin manifest.
 *
 * @package  NavbarCartIcons
 * @license  http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

// Only ever read by Plugin Manager, inside the admin, where this is defined.
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/**
 * Links shown in the Plugin Manager, alongside Install / Uninstall / Disable.
 *
 * Zen Cart stores `pluginDescription` in `plugin_control.description` (a TEXT
 * column) and echoes it into the Plugin Manager's info box as raw HTML,
 * without escaping, on every release from v1.5.8 to v3.0.0. That box is also
 * where the action buttons live, so markup placed here appears exactly once,
 * right where the store owner is already looking, with no core file changed.
 *
 * The ReadMe link is built from DIR_WS_CATALOG rather than hard-coded, so it
 * works whatever the store lives at and whatever the admin directory has been
 * renamed to. Zen Cart's shipped `zc_plugins/.htaccess` denies everything then
 * explicitly re-allows `.html`, so readme.html is reachable by design.
 *
 * On v2.2 and later the description is refreshed from this file on every
 * Plugin Manager scan. On v1.5.8, v2.0 and v2.1 it is not: those releases
 * write plugin_control.description only on the INSERT that first creates the
 * row, and only this plugin's installer refreshes it there (at Install,
 * Upgrade and Uninstall). The one state-dependent part, the Settings button,
 * is handled below.
 */
$nciPluginDir = 'zc_plugins/NavbarCartIcons/v1.0.2/';
$nciReadmeUrl = (defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/') . $nciPluginDir . 'readme.html';
$nciGithubUrl = 'https://github.com/dbltoe/Navbar_Cart_Icons';

/**
 * The Zen Cart forum's support thread for this plugin.
 *
 * On v1.5.8, v2.0 and v2.1 the description is captured on the first scan
 * and otherwise never refreshed; this plugin's installer saves it again at
 * Install, Upgrade and Uninstall, so a store gets the link on its next
 * Upgrade. An empty string renders nothing at all.
 *
 * The forum's thread address is /threads/<id>; the permalink below opens on
 * the first post, which is the authoritative description of the plugin. The
 * older /showthread.php?t=<id> form no longer resolves to the same thread,
 * so don't use it.
 */
$nciForumUrl = 'https://www.zen-cart.com/threads/207454?page=1#post-1347785';

/**
 * "Installed, but switched off."
 *
 * NAVBAR_CART_ICONS_STATUS is a configuration constant, defined during
 * application_top -- which runs before plugin_manager.php re-reads this
 * file. So:
 *
 *   - constant undefined  -> not installed. Say nothing.
 *   - defined and 'true'  -> live. Say nothing.
 *   - defined, not 'true' -> installed and dormant. Say so, in the NAME.
 *
 * The name and not the description, because plugin_control.description is
 * frozen at first insert on v1.5.8/v2.0/v2.1 and a notice there would never
 * clear. The name is kept honest on every release by the plugin's own admin
 * extra_functions file. It is plain text by necessity: plugin_control.name is
 * varchar(64) and the plugin list echoes it unescaped, so markup could be
 * truncated mid-tag.
 */
$nciIsOff = defined('NAVBAR_CART_ICONS_STATUS') && NAVBAR_CART_ICONS_STATUS !== 'true';

$nciName = 'Navbar Cart Icons';
if ($nciIsOff) {
    $nciName .= ' - Mod Not Turned On';
}

/**
 * The Settings button: this plugin's Configuration page, shown only once the
 * plugin is installed. Its gID is assigned at install, so it differs from
 * store to store and can't be written here; it's read from the admin page the
 * installer registers (page key configNavbarCartIcons). The link is relative
 * ("index.php?cmd=configuration&gID=N") because Plugin Manager is an admin
 * page, so it survives a domain or admin-folder rename.
 *
 * Zen Cart reads manifests only on the Plugin Manager page, but the lookup is
 * still guarded so a manifest read anywhere else (no database, CLI) just
 * leaves the button out. On v2.2 and later this file is re-read on every
 * Plugin Manager visit, so the button follows the install state by itself;
 * on v1.5.8, v2.0 and v2.1 the installer saves the re-read description at
 * Install, Upgrade and Uninstall (nciRefreshPluginDescription).
 */
$nciSettingsUrl = '';
if (defined('IS_ADMIN_FLAG') && IS_ADMIN_FLAG === true && defined('TABLE_ADMIN_PAGES')
    && isset($GLOBALS['db']) && is_object($GLOBALS['db'])) {
    $nciPage = $GLOBALS['db']->Execute(
        "SELECT main_page, page_params FROM " . TABLE_ADMIN_PAGES
        . " WHERE page_key = 'configNavbarCartIcons' LIMIT 1"
    );
    if (!$nciPage->EOF && defined($nciPage->fields['main_page'])) {
        $nciSettingsUrl = 'index.php?cmd=' . constant($nciPage->fields['main_page'])
            . '&' . $nciPage->fields['page_params'];
    }
}

/**
 * Styled as buttons matching Plugin Manager's own controls, which use exactly
 * `class="btn btn-primary" role="button"`.
 *
 * One row: ReadMe | Settings | Forum Thread | GitHub. A second line would push
 * the Author line and the core buttons further down the panel. Spacing is set
 * inline, which beats any theme `.btn` margin: every gap is 4px (the wrapper
 * supplies the space before the first button, each button the space after
 * itself) and the side padding is 8px rather than Bootstrap's 12px, so all
 * four fit on one line in Plugin Manager's side panel from a 1366px-wide
 * window up. Settings opens in the same tab (it's the admin itself); the
 * others open in a new one. Forum Thread renders nothing while $nciForumUrl
 * is empty.
 */
$nciButtonGap = '4px';
$nciButtonStyle = ' style="margin:0 ' . $nciButtonGap . ' 0 0;padding-left:8px;padding-right:8px"';
$nciButton = static function ($url, $label, $newTab) use ($nciButtonStyle) {
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"'
        . ($newTab ? ' target="_blank" rel="noopener noreferrer"' : '')
        . ' class="btn btn-primary" role="button"' . $nciButtonStyle . '>' . $label . '</a>';
};

$nciLinks =
    '<div style="margin:10px 0 0;padding:0 0 0 ' . $nciButtonGap . '">'
    . $nciButton($nciReadmeUrl, 'ReadMe', true)
    . ($nciSettingsUrl !== '' ? $nciButton($nciSettingsUrl, 'Settings', false) : '')
    . ($nciForumUrl !== '' ? $nciButton($nciForumUrl, 'Forum Thread', true) : '')
    . $nciButton($nciGithubUrl, 'GitHub', true)
    . '</div>';

return [
    'pluginVersion' => 'v1.0.2',
    'pluginName' => $nciName,
    'pluginDescription' =>
        'Puts a Shopping Cart icon and a Checkout icon beside the hamburger button of '
        . 'a ZCA Bootstrap navbar on phones and tablets, whenever the cart has something '
        . 'in it, so a customer needn\'t open the menu to get to either page. Works with '
        . 'ZCA Bootstrap and its clones out of the box. No template file is changed: '
        . 'install, and they are there.'
        . $nciLinks,
    // Shown as the Author in Plugin Manager, and stored in
    // plugin_control.author / plugin_control_versions.author (varchar(64)).
    'pluginAuthor' => 'My Zen Cart Host (dbltoe)',
    // ID from the Zen Cart Plugins Library. It is written to
    // plugin_control.zc_contrib_id and is the only thing that makes "a new
    // version is available" work in Plugin Manager. On v1.5.8/v2.0/v2.1 the
    // column is written only by the INSERT that first creates the row, so it
    // has to be right the first time a store installs. Zero until the Library
    // accepts the listing and assigns one.
    'pluginId' => 0,
    'zcVersions' => ['v158', 'v200', 'v210', 'v220', 'v230', 'v300'],
    'changelog' => 'changelog.txt',
    'github_repo' => $nciGithubUrl,
    'pluginGroups' => [],
];
