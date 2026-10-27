<?php
/**
 * Navbar Cart Icons -- storefront functions.
 *
 * Puts a Shopping Cart icon and a Checkout icon beside the hamburger button
 * of a ZCA Bootstrap navbar, on the screen sizes where the navbar is
 * collapsed, so a customer with something in the cart needn't open the menu
 * to reach either page.
 *
 * No notifier fires inside tpl_header.php on any Zen Cart release, and a
 * zc_plugin can't override a template file, so the page is captured in an
 * output buffer between NOTIFY_HTML_HEAD_END and NOTIFY_FOOTER_END. The
 * buffer's callback -- nci_ob_callback() below -- finds the navbar's toggler
 * button and inserts the icons just ahead of it. Everything that touches the
 * page is a pure function of the page and the settings, so it can be run over
 * captured storefront output with no Zen Cart install at all.
 *
 * A page that already carries an element with id="navbarCartIcons" -- a
 * template still holding a hand edit of the same thing -- is left alone.
 *
 * @package  NavbarCartIcons
 * @license  http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

/* ------------------------------------------------------------------ *
 * The markup this plugin looks for
 * ------------------------------------------------------------------ */

/**
 * ZCA Bootstrap: an opening <nav> tag carrying the `navbar` class. The
 * breadcrumb is also a <nav> but has no such class.
 */
if (!defined('NAVBAR_CART_ICONS_PATTERN_NAV')) {
    define('NAVBAR_CART_ICONS_PATTERN_NAV', '~<nav\b[^>]*\bclass="[^"]*\bnavbar\b[^"]*"[^>]*>~');
}

/**
 * The hamburger: the navbar's toggler button. The icons go just ahead of it.
 */
if (!defined('NAVBAR_CART_ICONS_PATTERN_TOGGLER')) {
    define('NAVBAR_CART_ICONS_PATTERN_TOGGLER', '~<button\b[^>]*\bclass="[^"]*\bnavbar-toggler\b[^"]*"[^>]*>~');
}

/* ------------------------------------------------------------------ *
 * Settings
 * ------------------------------------------------------------------ */

/**
 * A configuration value, or a default when the key is not defined.
 *
 * Deliberately not zen_config(): that helper is v3.0.0 only and a fatal
 * "undefined function" on everything earlier. This is its own fallback form.
 *
 * @param string $key
 * @param string $default
 * @return string
 */
function nci_config($key, $default = '')
{
    return defined($key) ? (string)constant($key) : $default;
}

/**
 * @return bool
 */
function nci_is_enabled()
{
    return nci_config('NAVBAR_CART_ICONS_STATUS', 'false') === 'true';
}

/**
 * The character set Zen Cart is running in.
 *
 * @return string
 */
function nci_charset()
{
    return defined('CHARSET') ? CHARSET : 'utf-8';
}

/**
 * Make a value safe inside a double-quoted attribute, exactly once.
 *
 * Language constants and configuration values may already carry entities
 * (Zen Cart's admin stores `&` as `&amp;`, and zen_href_link() returns
 * `&amp;` between parameters), so the value is decoded first and then
 * escaped once. Right whether the input was escaped, raw, or a mixture.
 *
 * @param string $value
 * @return string
 */
function nci_attr($value)
{
    $decoded = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, nci_charset());

    return htmlspecialchars($decoded, ENT_QUOTES | ENT_HTML5, nci_charset());
}

/**
 * A CSS color, checked before it goes inside <style>; '' when it doesn't
 * look like one (or was left empty, which means "inherit the navbar's").
 *
 * @param string $value
 * @return string
 */
function nci_css_color($value)
{
    $value = trim((string)$value);
    if ($value !== '' && preg_match('~^(#[0-9a-f]{3,8}|[a-z]{3,30}|(rgb|hsl)a?\([0-9%.,\s/]+\)|var\(--[a-z0-9_-]+\))$~i', $value) === 1) {
        return $value;
    }

    return '';
}

/**
 * A CSS length, checked before it goes inside <style>.
 *
 * @param string $value
 * @param string $default
 * @return string
 */
function nci_css_length($value, $default)
{
    $value = trim((string)$value);
    if (preg_match('~^(\d*\.?\d+(px|em|rem|%|pt|vw|vh)|0)$~i', $value) === 1) {
        return $value;
    }

    return $default;
}

/**
 * A Font Awesome class list, checked before it goes inside an attribute:
 * letters, digits, spaces, hyphens and underscores only.
 *
 * @param string $value
 * @param string $default
 * @return string
 */
function nci_icon_class($value, $default)
{
    $value = trim((string)$value);
    if (preg_match('~^[a-z0-9][a-z0-9 _-]{0,79}$~i', $value) === 1) {
        return $value;
    }

    return $default;
}

/**
 * Every setting, read once per request and already validated.
 *
 * @return array
 */
function nci_options()
{
    return [
        'show_cart' => nci_config('NAVBAR_CART_ICONS_SHOW_CART', 'true') === 'true',
        'show_checkout' => nci_config('NAVBAR_CART_ICONS_SHOW_CHECKOUT', 'true') === 'true',
        'when_empty' => nci_config('NAVBAR_CART_ICONS_WHEN_EMPTY', 'false') === 'true',
        'count_badge' => nci_config('NAVBAR_CART_ICONS_COUNT_BADGE', 'false') === 'true',
        'cart_class' => nci_icon_class(nci_config('NAVBAR_CART_ICONS_CART_CLASS', 'fas fa-shopping-cart'), 'fas fa-shopping-cart'),
        'checkout_class' => nci_icon_class(nci_config('NAVBAR_CART_ICONS_CHECKOUT_CLASS', 'fas fa-credit-card'), 'fas fa-credit-card'),
        'shrink_brand' => nci_config('NAVBAR_CART_ICONS_SHRINK_BRAND', 'true') === 'true',
        'load_css' => nci_config('NAVBAR_CART_ICONS_LOAD_CSS', 'true') === 'true',
        'color' => nci_css_color(nci_config('NAVBAR_CART_ICONS_COLOR', '')),
        'hover' => nci_css_color(nci_config('NAVBAR_CART_ICONS_COLOR_HOVER', '')),
        'size' => nci_css_length(nci_config('NAVBAR_CART_ICONS_SIZE', '1.5rem'), '1.5rem'),
        'gap' => nci_css_length(nci_config('NAVBAR_CART_ICONS_GAP', '10px'), '10px'),
    ];
}

/* ------------------------------------------------------------------ *
 * What the store knows: the cart, the links, the labels
 * ------------------------------------------------------------------ */

/**
 * How many items are in the visitor's cart, as the cart itself counts them.
 *
 * @return int|float 0 when there is no cart
 */
function nci_cart_count()
{
    if (isset($_SESSION['cart']) && is_object($_SESSION['cart']) && method_exists($_SESSION['cart'], 'count_contents')) {
        $count = $_SESSION['cart']->count_contents();
        if (is_numeric($count) && $count > 0) {
            return $count + 0;
        }
    }

    return 0;
}

/**
 * The count as shown on the badge: a whole number when it is one.
 *
 * @param int|float $count
 * @return string
 */
function nci_count_text($count)
{
    if ((float)$count === floor((float)$count)) {
        return (string)(int)$count;
    }

    return rtrim(rtrim(number_format((float)$count, 2, '.', ''), '0'), '.');
}

/**
 * Where the two icons go: the same two pages ZCA Bootstrap's own menu links
 * to, built with the same zen_href_link() calls.
 *
 * @return array ['cart' => url, 'checkout' => url], '' where unavailable
 */
function nci_links()
{
    $links = ['cart' => '', 'checkout' => ''];
    if (!function_exists('zen_href_link')) {
        return $links;
    }
    if (defined('FILENAME_SHOPPING_CART')) {
        $links['cart'] = (string)zen_href_link(FILENAME_SHOPPING_CART, '', 'NONSSL');
    }
    if (defined('FILENAME_CHECKOUT_SHIPPING')) {
        $links['checkout'] = (string)zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL');
    }

    return $links;
}

/**
 * The accessible names of the two icons: the same language constants the
 * menu uses for its Shopping Cart and Checkout entries, so a store that has
 * reworded or translated them is followed.
 *
 * @return array
 */
function nci_labels()
{
    return [
        'cart' => defined('HEADER_TITLE_CART_CONTENTS') ? (string)HEADER_TITLE_CART_CONTENTS : 'Shopping Cart',
        'checkout' => defined('HEADER_TITLE_CHECKOUT') ? (string)HEADER_TITLE_CHECKOUT : 'Checkout',
    ];
}

/**
 * Is there anything to show on this request at all?
 *
 * The checkout icon needs something in the cart; the cart icon needs that
 * too unless the owner asked for it always. When neither can show, the page
 * is never buffered.
 *
 * @param array     $options from nci_options()
 * @param int|float $count   from nci_cart_count()
 * @return bool
 */
function nci_wants_render(array $options, $count)
{
    $cart = !empty($options['show_cart']) && ($count > 0 || !empty($options['when_empty']));
    $checkout = !empty($options['show_checkout']) && $count > 0;

    return $cart || $checkout;
}

/* ------------------------------------------------------------------ *
 * The markup this plugin adds
 * ------------------------------------------------------------------ */

/**
 * The Bootstrap breakpoint at which this navbar expands, read from its own
 * `navbar-expand-*` class. ZCA Bootstrap ships `navbar-expand-lg`; a store
 * that changed it gets icons that come and go with its hamburger.
 *
 * @param string $navTag the opening <nav> tag
 * @return string sm | md | lg | xl
 */
function nci_breakpoint($navTag)
{
    if (preg_match('~\bnavbar-expand-(sm|md|lg|xl)\b~', $navTag, $m) === 1) {
        return $m[1];
    }

    return 'lg';
}

/**
 * The widest viewport at which a navbar with that breakpoint is collapsed.
 *
 * @param string $breakpoint
 * @return string a CSS length for max-width
 */
function nci_breakpoint_max($breakpoint)
{
    $max = ['sm' => '575.98px', 'md' => '767.98px', 'lg' => '991.98px', 'xl' => '1199.98px'];

    return $max[$breakpoint] ?? $max['lg'];
}

/**
 * The element inserted ahead of the hamburger: a Bootstrap nav list holding
 * up to two icon links. '' when nothing is to be shown.
 *
 * `ml-auto` pushes the list to the right, against the toggler, whatever
 * comes before it; `d-<bp>-none` hides it once the navbar expands and its
 * own Shopping Cart and Checkout entries are in view. The `ms-auto` twin is
 * for a template on Bootstrap 5, which renamed the utility.
 *
 * @param int|float $count
 * @param array     $links      from nci_links()
 * @param array     $labels     from nci_labels()
 * @param array     $options    from nci_options()
 * @param string    $breakpoint from nci_breakpoint()
 * @return string
 */
function nci_icons_html($count, array $links, array $labels, array $options, $breakpoint = 'lg')
{
    $items = '';

    $showCart = !empty($options['show_cart']) && ($count > 0 || !empty($options['when_empty'])) && $links['cart'] !== '';
    if ($showCart) {
        $label = nci_attr($labels['cart']);
        $badge = '';
        if (!empty($options['count_badge']) && $count > 0) {
            $shown = nci_count_text($count);
            $badge = '<span class="nciCount" aria-hidden="true">' . $shown . '</span>';
            $label .= ' (' . $shown . ')';
        }
        $items .= '<li class="nav-item" title="' . $label . '">'
            . '<a class="nav-link" href="' . nci_attr($links['cart']) . '" aria-label="' . $label . '">'
            . '<i class="' . nci_attr($options['cart_class']) . '" aria-hidden="true"></i>' . $badge
            . '</a></li>';
    }

    $showCheckout = !empty($options['show_checkout']) && $count > 0 && $links['checkout'] !== '';
    if ($showCheckout) {
        $label = nci_attr($labels['checkout']);
        $items .= '<li class="nav-item" title="' . $label . '">'
            . '<a class="nav-link" href="' . nci_attr($links['checkout']) . '" aria-label="' . $label . '">'
            . '<i class="' . nci_attr($options['checkout_class']) . '" aria-hidden="true"></i>'
            . '</a></li>';
    }

    if ($items === '') {
        return '';
    }

    return '<ul id="navbarCartIcons" class="navbar-nav flex-row ml-auto ms-auto d-' . $breakpoint . '-none">' . $items . '</ul>';
}

/**
 * The owner's appearance settings as one <style> element for <head>.
 *
 * Every value in it has passed a validator on its way through
 * nci_options(); nothing here is printed unchecked. '' when the plugin
 * stylesheet is switched off, in which case the template's CSS is all
 * there is.
 *
 * The last rule is the one that keeps the row on one line. The navbar is a
 * wrapping flex container, and a brand or trademark mark of fixed width
 * ahead of the icons would push the hamburger onto a second line on a narrow
 * phone or a large-text setting. Giving that first item a zero flex basis
 * lets it take whatever room is left and clip with an ellipsis rather than
 * wrap the controls. It applies only while the navbar is collapsed, so the
 * desktop layout is untouched. `#navMain` is there for specificity: it
 * outranks a template's or another plugin's `nav.navbar .something` rule.
 *
 * @param array  $options    from nci_options()
 * @param string $breakpoint from nci_breakpoint()
 * @return string
 */
function nci_head_style(array $options, $breakpoint = 'lg')
{
    if (empty($options['load_css'])) {
        return '';
    }

    $css = '#navbarCartIcons{margin-right:' . $options['gap'] . '}'
        . '#navbarCartIcons .nav-item+.nav-item{margin-left:' . $options['gap'] . '}'
        . '#navbarCartIcons .nav-link{font-size:' . $options['size']
        . ($options['color'] !== '' ? ';color:' . $options['color'] : '') . '}';

    if ($options['hover'] !== '') {
        $css .= '#navbarCartIcons .nav-link:hover,#navbarCartIcons .nav-link:focus{color:' . $options['hover'] . '}';
    }

    if (!empty($options['shrink_brand'])) {
        $css .= '@media (max-width:' . nci_breakpoint_max($breakpoint) . '){'
            . '#navMain nav.navbar>:first-child:not(.navbar-toggler):not(.navbar-collapse):not(#navbarCartIcons)'
            . '{flex:1 1 0;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}}';
    }

    return '<style id="navbarCartIconsStyle">' . $css . '</style>';
}

/**
 * Put the icons (and the appearance rule) into a rendered page.
 *
 * Pure: takes a page and everything the markup is built from, returns the
 * page and whether anything was placed. Nothing here reads Zen Cart state.
 *
 * Rules:
 *
 *   0. A page that already carries id="navbarCartIcons" is left alone. That
 *      is a template still holding a hand edit of the same thing; a second
 *      set of icons would be worse than none.
 *   1. The first <nav class="... navbar ..."> that holds a navbar-toggler
 *      button gets the icons, immediately ahead of that button. A navbar
 *      with no toggler never collapses, so it is skipped, and a page with no
 *      such navbar at all (responsive_classic, say) is returned untouched.
 *   2. The stylesheet link and the <style> go in just before </head>, or,
 *      if the page has no </head> in the buffered part, ahead of the icons
 *      themselves. Neither is added to a page that gets no icons, so a
 *      template with no such navbar pays nothing at all.
 *
 * @param string    $html    the rendered page
 * @param int|float $count   from nci_cart_count()
 * @param array     $links   from nci_links()
 * @param array     $labels  from nci_labels()
 * @param array     $options from nci_options()
 * @param string    $link    from nci_render_head(): the stylesheet link, or ''
 * @return array ['html' => string, 'placed' => bool, 'breakpoint' => string]
 */
function nci_inject($html, $count, array $links, array $labels, array $options, $link = '')
{
    $result = ['html' => $html, 'placed' => false, 'breakpoint' => 'lg'];

    if (preg_match('~\bid="navbarCartIcons"~', $html) === 1) {
        return $result;
    }

    if (preg_match_all(NAVBAR_CART_ICONS_PATTERN_NAV, $html, $navs, PREG_OFFSET_CAPTURE) < 1) {
        return $result;
    }

    foreach ($navs[0] as $nav) {
        $navTag = $nav[0];
        $start = $nav[1] + strlen($navTag);
        $end = stripos($html, '</nav>', $start);
        if ($end === false) {
            $end = strlen($html);
        }
        $inside = substr($html, $start, $end - $start);

        if (preg_match(NAVBAR_CART_ICONS_PATTERN_TOGGLER, $inside, $toggler, PREG_OFFSET_CAPTURE) !== 1) {
            continue;
        }

        $breakpoint = nci_breakpoint($navTag);
        $icons = nci_icons_html($count, $links, $labels, $options, $breakpoint);
        if ($icons === '') {
            return $result;
        }

        $style = nci_head_style($options, $breakpoint);
        $head = (empty($options['load_css']) ? '' : (string)$link) . $style;
        $at = $start + $toggler[0][1];
        $page = substr($html, 0, $at) . $icons . substr($html, $at);

        if ($head !== '') {
            $headEnd = stripos($page, '</head>');
            if ($headEnd !== false && $headEnd < $at) {
                $page = substr($page, 0, $headEnd) . $head . "\n" . substr($page, $headEnd);
            } else {
                $page = substr($page, 0, $at) . $head . substr($page, $at);
            }
        }

        $result['html'] = $page;
        $result['placed'] = true;
        $result['breakpoint'] = $breakpoint;

        return $result;
    }

    return $result;
}

/**
 * Output-buffer callback: the page passes through here on its way out.
 *
 * Registered by the observer with ob_start() at NOTIFY_HTML_HEAD_END. PHP
 * calls it when the buffer is flushed -- normally by the observer at
 * NOTIFY_FOOTER_END, or at script end if that notifier never fires (a custom
 * template that dropped it, say). Either way the icons are inserted.
 *
 * Partial flushes are folded back together. If something between the two
 * notifiers calls ob_flush(), PHP hands over what it has so far without the
 * FINAL flag; that is held here and prepended when the final chunk arrives,
 * so the patterns always see the whole page and never a header cut in half.
 *
 * @param string $buffer
 * @param int    $phase  PHP_OUTPUT_HANDLER_* flags
 * @return string
 */
function nci_ob_callback($buffer, $phase = 0)
{
    static $held = '';

    if (($phase & PHP_OUTPUT_HANDLER_FINAL) === 0 && ($phase & PHP_OUTPUT_HANDLER_END) === 0) {
        $held .= $buffer;
        return '';
    }

    $page = $held . $buffer;
    $held = '';

    $result = nci_inject($page, nci_cart_count(), nci_links(), nci_labels(), nci_options(), nci_render_head());

    return $result['html'];
}

/* ------------------------------------------------------------------ *
 * The <head> additions
 * ------------------------------------------------------------------ */

/**
 * A web path for a catalog-relative file path.
 *
 * @param string $relative
 * @return string
 */
function nci_catalog_path($relative)
{
    $base = defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/';

    return $base . ltrim($relative, '/');
}

/**
 * Where the stylesheet is: the active template's own copy if it has one,
 * otherwise the copy shipped inside this plugin.
 *
 * The plugin path is worked out from this file's location, so the version
 * directory can change without anything here being edited. Zen Cart's
 * shipped zc_plugins/.htaccess denies everything then re-allows .css, which
 * is what lets a browser fetch it from there.
 *
 * @return string '' if neither file exists
 */
function nci_stylesheet_href()
{
    $filename = 'navbar_cart_icons.css';
    $catalog = defined('DIR_FS_CATALOG') ? DIR_FS_CATALOG : '';

    if (defined('DIR_WS_TEMPLATE')) {
        $templatePath = DIR_WS_TEMPLATE . 'css/' . $filename;
        if ($catalog !== '' && is_file($catalog . $templatePath)) {
            return nci_catalog_path($templatePath);
        }
    }

    $pluginRoot = dirname(__DIR__, 4);
    $pluginRelative = 'zc_plugins/' . basename(dirname($pluginRoot)) . '/' . basename($pluginRoot)
        . '/catalog/includes/templates/template_default/css/' . $filename;

    if ($catalog !== '' && is_file($catalog . $pluginRelative)) {
        return nci_catalog_path($pluginRelative);
    }

    return '';
}

/**
 * The stylesheet link for <head>.
 *
 * Handed to nci_inject() by the buffer callback, which puts it just before
 * </head> together with the owner's settings as an inline rule -- and only
 * on a page that actually gets the icons. Nothing is written at
 * NOTIFY_HTML_HEAD_END itself: at that point the navbar hasn't been rendered
 * yet, so neither the breakpoint nor whether there is a navbar at all is
 * known. '' when "Load The Plugin Stylesheet?" is off.
 *
 * @return string
 */
function nci_render_head()
{
    if (nci_config('NAVBAR_CART_ICONS_LOAD_CSS', 'true') !== 'true') {
        return '';
    }

    $href = nci_stylesheet_href();
    if ($href === '') {
        return '';
    }

    return '<link rel="stylesheet" href="' . htmlspecialchars($href, ENT_QUOTES, nci_charset()) . '">' . "\n";
}
