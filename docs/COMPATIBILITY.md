# Compatibility notes: Zen Cart v1.5.8 → v3.0.0, PHP 7.4 → 8.5

This plugin runs unmodified across six Zen Cart release branches. That comes
from choosing, at each decision point, the mechanism that exists in *every*
supported version rather than the newest one. This document records those
choices so a future maintainer knows which are load-bearing.

## How it's verified

Not by assertion. The maintainer's harness (not shipped) checks, on every PHP
build from 7.4 to 8.5:

| Check | Method |
|---|---|
| **Placement** | Captured storefront pages — a live ZCA Bootstrap 3.7.6 clone on Zen Cart 2.2.0 with the Trademark plugin's mark already in the navbar, the ZCA Bootstrap 3.8.0 header with its PHP resolved, and responsive_classic pages that must be left untouched — are run through the insertion function and the output inspected byte for byte, including that removing the insertions gives the original page back exactly. |
| **Zen Cart API** | Every `zen_*` function the plugin calls is looked up in all six installs. Both notifiers it observes are confirmed to fire from `template_default` on all six. The two language constants, two filename constants and the cart method it reads are confirmed present on all six. |
| **Observer** | Real PHP output buffering, end to end: the normal sequence, a partial flush between the notifiers, a buffer somebody else left open, the footer notifier never firing, the switched-off state and the empty-cart state. |
| **Installer** | Against a recording fake database: the SQL emitted, its order, and the columns every row carries. Also that exactly the three ScriptedInstaller hooks are declared and no auto_loader exists. |
| **Security** | The Plugin Library's own scan questions, plus the escaping and validation paths specific to this plugin, checked structurally so a shortcut added later fails the suite. |
| **PHP** | Lint plus the whole suite on 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 and 8.5, with zero diagnostics at `E_ALL`. |

---

## What we rely on

### No header notifier exists — so the page is buffered

There is no `$zco_notifier->notify()` call anywhere inside `tpl_header.php` on
any supported release or any ZCA Bootstrap release, and a zc_plugin cannot
supply a template override. The two notifiers that bracket the page body and
exist on every release are:

| Notifier | Fires | Present |
|---|---|---|
| `NOTIFY_HTML_HEAD_END` | `html_header.php`, just before `</head>` | v1.5.8 → v3.0.0, and ZCA Bootstrap's own `html_header.php` |
| `NOTIFY_FOOTER_END` | `tpl_main_page.php`, just before `</body>` | v1.5.8 → v3.0.0, and ZCA Bootstrap's own `tpl_main_page.php` |

The observer calls `ob_start('nci_ob_callback')` at the first; at the second
it calls `ob_end_flush()`, and the callback inserts the icons ahead of the
toggler and the stylesheet link plus the inline rule just before `</head>`.
Everything from `</head>` to `</body>` passes through the callback once, and a
page that gets no icons gets nothing else either.

Three things make this safe:

- **The observer closes only its own buffer.** It records `ob_get_level()`
  after opening, and at the footer closes the buffer only if that is still the
  innermost level. If something between the two notifiers opened a buffer and
  left it open, both are left for PHP to close at script end — where the
  callback still runs.
- **Partial flushes are reassembled.** If anything calls `ob_flush()` between
  the notifiers, PHP hands the callback what it has so far without the FINAL
  flag. The callback holds that and prepends it when the final chunk arrives,
  so the patterns always see the whole page and never a header cut in half.
- **Nothing happens when nothing can show.** The observer asks
  `nci_wants_render()` first; with an empty cart (and the empty-cart icon off)
  no link is written and no buffer is opened. Most page views cost nothing.

Zen Cart's own `init_gzip.php` opens `ob_gzhandler` in `application_top`, so
when compression is on the plugin's buffer nests inside it and hands the
compressor the transformed page.

### The ZCA Bootstrap navbar markup hasn't changed

```
<nav class="navbar fixed-top mx-3 navbar-expand-lg rounded-bottom" ...>
    <button class="navbar-toggler" type="button" ...>
```

is the same in every ZCA Bootstrap release from 3.3.0 through 3.8.0 (checked
at the 3.4.0, 3.6.0, 3.7.0, 3.7.6 and 3.8.0 tags). The plugin matches a
`<nav>` carrying the `navbar` class, then the first `navbar-toggler` button
inside it, and inserts ahead of the button. The breadcrumb `<nav>` has no
`navbar` class and is never matched; a navbar with no toggler is skipped.

The breakpoint is read from the `navbar-expand-(sm|md|lg|xl)` class on the
same tag and turned into `d-<bp>-none` on the list and a `max-width` media
query for the one-line rule, using Bootstrap 4's own boundaries (575.98,
767.98, 991.98, 1199.98px). Bootstrap 5 keeps the same boundaries; the list
carries both `ml-auto` and `ms-auto` so it is pushed right on either.

### The two pages and the two labels are core

`FILENAME_SHOPPING_CART`, `FILENAME_CHECKOUT_SHIPPING`,
`HEADER_TITLE_CART_CONTENTS` and `HEADER_TITLE_CHECKOUT` are defined by core
on every release from v1.5.8 (the last two in `lang.header.php`), and
`shoppingCart::count_contents()` exists throughout. The plugin guards every
one with `defined()` / `method_exists()` anyway and falls back to plain
English labels, so a store missing one still renders.

### Font Awesome 5 and 6 both know the default icon names

ZCA Bootstrap 3.3–3.5 ship Font Awesome 5, 3.6+ ship 6. `fas fa-shopping-cart`
and `fas fa-credit-card` are valid in both (6 keeps the 5 names as aliases),
so the defaults render on every ZCA release. The 6-only `fa-solid
fa-cart-shopping` form is not used.

### Everything that reaches the page is escaped or validated

Attribute values (the two hrefs, the two labels, the two class lists) pass
through `nci_attr()`, which decodes then escapes exactly once — right whether
the input was already escaped (`zen_href_link()` returns `&amp;`), raw, or a
mixture. The four settings that reach `<style>` pass validators
(`nci_css_color()`, `nci_css_length()`) and the two class settings pass
`nci_icon_class()`; anything that fails falls back to the default rather than
being printed. The breakpoint is one of four fixed words. None of the
configuration rows needs a `val_function`.

### Observer registration: `auto.*.php` + `zcObserver*`

`includes/init_includes/init_observers.php` scans each installed plugin's
`catalog/includes/classes/observers/` for `auto.*.php`, includes them, and
instantiates `'zcObserver' . base::camelize($name, true)`. Identical in v1.5.8
and v3.0.0-dev. The plugin ships no `auto_loaders/` directory at all: a second
registration there would include the file twice and fatal every page after
install.

### The functions file is loaded by the observer itself

`includes/modules/extra_functions.php` loads an installed plugin's
`catalog/includes/functions/extra_functions/` on every release, but the
observer doesn't rely on it: it `require_once`s the file by path if the
functions aren't there yet. Either way the file loads exactly once.

### Stylesheet linked from inside `zc_plugins/`

Zen Cart's shipped `zc_plugins/.htaccess` denies everything then explicitly
re-allows a list that includes `css`, so the stylesheet can be served from the
plugin's own directory on every release. The template's
`css/navbar_cart_icons.css` is checked first so a store can override it. The
alternative, `linkCatalogStylesheet()` from the `InteractsWithPlugins` trait,
is v2.x-only.

### Language file: `lang.*.php` returning an array

v1.5.8 introduced the array-returning language format and every release since
reads it from a plugin's `admin/includes/languages/<lang>/extra_definitions/`.
v3.0.0 drops the legacy `define()` loader, so this is the only format that
spans the range. The plugin has one admin string and no storefront strings of
its own — the labels are core's.

### Installer: `executeInstallerSql()` only

`addConfigurationKey()`, `getOrCreateConfigGroupId()` and friends were added in
v2.0.1/v2.1.0 and don't exist on v1.5.8. The installer uses raw SQL through
`executeInstallerSql()` throughout, every table name is a `TABLE_*` constant
so a store with a database prefix works, and `executeUpgrade()` takes an
optional argument because v1.5.8 calls it with none. Only the three hooks
Zen Cart actually calls — `executeInstall`, `executeUpgrade`,
`executeUninstall` — are declared; anything else would sit there unrun.

### `plugin_control.name` isn't refreshed on half the range

`PluginManager::updatePluginControl()` refreshes `name` and `description` on
every scan on v2.2+ (`upsertMany()`), but on v1.5.8/v2.0/v2.1 it calls
Eloquent's `upsert($values, ['id'], ['infs'])`, which rewrites only `infs`. So
the *Mod Not Turned On* notice in the name would freeze on those releases. The
plugin's `admin/includes/functions/extra_functions/navbar_cart_icons_admin.php`,
which those releases load on every admin page, corrects the stored name on the
Plugin Manager page when it disagrees with `NAVBAR_CART_ICONS_STATUS`, and the
uninstaller puts the plain name back. The same reasoning is why nothing
state-dependent is in `pluginDescription`.

### `zen_config()` is not used

It's `@since v3.0.0` and a fatal on everything earlier. `nci_config()` is its
portable form: `defined($key) ? constant($key) : $default`.

### The PHP floor is 7.4

Zen Cart v1.5.8 runs on PHP 7.4 and 8.0 (not 8.1: it fatals in
`application_top` on 8.1's `IntlDateFormatter` change), so the plugin's source
uses nothing newer than 7.4: no `str_contains()`, no `match`, no nullsafe
operator, no named arguments, no union types. The harness greps for each. It
also stays clean on 8.4/8.5: no implicitly-nullable parameters, and the
observer declares its one property.

---

## What isn't relied on

- `NOTIFY_PAGE_BODY_BEGIN` (v2.1+) — would be a tidier place to open the buffer.
- `NOTIFY_HTML_HEAD_CSS_BEGIN` (v2.1+) — would put the link among the other
  stylesheets rather than after them.
- `InteractsWithPlugins::linkCatalogStylesheet()` (v2.x).
- Root-level `filenames.php` / `database_tables.php` (v2.2+). The plugin needs
  neither.
- `$_SESSION['layoutType']` or any device detection. The icons are hidden by
  Bootstrap's own responsive utilities at the navbar's own breakpoint.
- The `:has()` selector. The one-line rule targets the navbar's first child
  directly, so it works in browsers without `:has()` support.
