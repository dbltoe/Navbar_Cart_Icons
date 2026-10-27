<?php
/**
 * Navbar Cart Icons -- storefront observer.
 *
 * Auto-loaded and instantiated by `includes/init_includes/init_observers.php`
 * on every supported Zen Cart release. The filename
 * (`auto.navbar_cart_icons.php`) and the class name must stay in step: Zen
 * Cart derives the expected class as
 * 'zcObserver' . base::camelize('navbar_cart_icons', true).
 *
 * Two notifiers, both present from v1.5.8 through v3.0.0-dev:
 *
 *   NOTIFY_HTML_HEAD_END   just before </head>. An output buffer is opened so
 *                          that everything from here to the end of the page
 *                          passes through nci_ob_callback() on its way out.
 *   NOTIFY_FOOTER_END      just before </body>. The buffer is closed, which is
 *                          when the callback runs and the icons, the
 *                          stylesheet link and the appearance rule are
 *                          inserted -- or nothing is, on a page with no
 *                          navbar of the expected kind.
 *
 * If NOTIFY_FOOTER_END never fires -- a custom template that dropped it --
 * PHP flushes the buffer at script end and the callback runs then instead.
 * The icons still appear; the page is merely held a little longer.
 *
 * Nothing at all happens on a request with nothing to show: an empty cart
 * (unless the owner asked for the cart icon regardless) opens no buffer.
 *
 * @package  NavbarCartIcons
 * @license  http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// The storefront functions. Zen Cart's includes/modules/extra_functions.php
// loads an installed plugin's catalog extra_functions on every release, but
// nothing here depends on it: the file is pulled in by path if it isn't
// there yet, and require_once makes a second load impossible.
if (!function_exists('nci_is_enabled')) {
    require_once dirname(__DIR__, 2) . '/functions/extra_functions/navbar_cart_icons_functions.php';
}

class zcObserverNavbarCartIcons extends base
{
    /**
     * The output-buffer nesting level this observer's buffer sits at, or 0
     * when it has none open.
     *
     * Recorded so the close can be certain it is closing its own buffer. If
     * something between the two notifiers opened a buffer of its own and
     * left it open, blindly calling ob_end_flush() here would close THAT one
     * and leave ours for script end. Checking the level first means the
     * wrong buffer is never touched.
     *
     * @var int
     */
    protected $bufferLevel = 0;

    public function __construct()
    {
        $this->attach($this, [
            'NOTIFY_HTML_HEAD_END',
            'NOTIFY_FOOTER_END',
        ]);
    }

    /**
     * End of <head>: start buffering the rest of the page.
     */
    public function updateNotifyHtmlHeadEnd($class, $eventID, $param1 = null)
    {
        if (!function_exists('nci_is_enabled') || !nci_is_enabled()) {
            return;
        }

        if ($this->bufferLevel > 0) {
            return;
        }

        if (!nci_wants_render(nci_options(), nci_cart_count())) {
            // Nothing could be shown on this request, so the page isn't
            // even buffered.
            return;
        }

        if (ob_start('nci_ob_callback')) {
            $this->bufferLevel = ob_get_level();
        }
    }

    /**
     * End of <body>: close the buffer, which inserts the icons.
     */
    public function updateNotifyFooterEnd($class, $eventID, $param1 = null)
    {
        if ($this->bufferLevel === 0) {
            return;
        }

        // Only close it if it is the innermost buffer. If it is not, someone
        // else's buffer is still open above ours; leave both for script end,
        // where PHP closes them in order and our callback still runs.
        if (ob_get_level() === $this->bufferLevel) {
            ob_end_flush();
        }

        $this->bufferLevel = 0;
    }
}
