# Installing Navbar Cart Icons

Applies to Zen Cart v1.5.8 and later, including v2.x and the v3.0.0 development
branch. PHP 7.4 through 8.5. Your store needs to be running ZCA Bootstrap
(3.3.0 or later) or a clone of it; on any other template the plugin installs
fine and does nothing.

---

## 1. Copy the files

This repository contains one directory that belongs in your store:

```
zc_plugins/NavbarCartIcons/
```

Upload it so it lands at:

```
<your store root>/zc_plugins/NavbarCartIcons/v1.0.0/
```

That directory should contain `manifest.php`, `readme.html`, `changelog.txt`,
and the `Installer/`, `admin/` and `catalog/` sub-directories.

Nothing goes anywhere else. The plugin doesn't drop files into your `admin/`,
`includes/` or template directories, which is what makes it safe to remove
later.

> **If your store is itself a git checkout of Zen Cart:** `zc_plugins/.gitignore`
> uses a deny-all rule with an explicit allowlist, so add `!NavbarCartIcons/`
> and `!NavbarCartIcons/**` to it or git won't see the plugin. This has no
> effect on the plugin working.

## 2. Install it

1. Log in to your Zen Cart admin.
2. Go to **Modules → Plugin Manager**.
3. Find **Navbar Cart Icons** and click **Install**.

> Selecting the plugin shows an info panel on the right. Alongside the
> description and the **Install / Uninstall / Disable** buttons you'll find
> **Read Me** and **GitHub** buttons. Read Me opens the full documentation, and
> both work whether or not the plugin is installed.

The installer creates a **Navbar Cart Icons** configuration group and adds it
to the Configuration menu. If the menu entry doesn't appear straight away, log
out of the admin and back in — Zen Cart caches the menu structure per session.

## 3. Look at your storefront on a phone

Add something to the cart. The cart and checkout icons are beside the
hamburger, in the navbar's own link color, at the same size as the hamburger's
bars. The master switch is **on** from the start, because with every other
setting at its default the plugin does exactly what it's for.

With an empty cart there's nothing to see, on purpose: ZCA Bootstrap's own menu
lists *Shopping Cart* and *Checkout* only once something is in the cart, and
the icons follow the same rule. **Show The Cart Icon When The Cart Is Empty?**
changes that if you'd rather have the cart icon always visible.

Every setting is documented in [CONFIGURATION.md](CONFIGURATION.md).

---

## Coming from a hand edit of tpl_header.php

If you added the icons to your template by hand before this plugin existed,
the edit and the plugin can coexist: the plugin looks for an element with
`id="navbarCartIcons"` and, finding one, adds nothing. So:

1. **Install the plugin first.** Nothing changes on the storefront yet.
2. Remove the hand edit from your cloned template's `tpl_header.php` and any
   `#navbarCartIcons` rules from its CSS, or leave the CSS if you prefer it to
   the plugin's settings (raise its specificity with `#navMain` so it wins).
3. Reload the storefront. The plugin's icons are now the ones you see, in the
   same spot.

There's never a moment with no icons showing.

---

## Upgrading the plugin

1. Upload the new version directory alongside the old one, so that
   `zc_plugins/NavbarCartIcons/` holds both. **Don't delete the old one yet** —
   Plugin Manager needs both to offer the upgrade.
2. **Modules → Plugin Manager → Upgrade.**
3. Once it reports success, the old version directory can be deleted.

**Your settings survive.** Configuration records are written with
`INSERT IGNORE` followed by an update of the metadata only — labels, help text,
ordering and input type are refreshed to the current version, but
`configuration_value` is never touched.

Uninstall + Install produces the same result if you prefer it.

---

## Uninstalling

**Modules → Plugin Manager → Navbar Cart Icons → Uninstall.** This removes the
configuration group, all of its settings, and the admin menu entry. No template
file was ever changed, so there's nothing else to undo.

To remove the code as well, delete the `zc_plugins/NavbarCartIcons/` directory
afterwards.

To hide the icons without uninstalling, set **Enable Navbar Cart Icons?** to
`false`. While it's off, Plugin Manager lists the plugin as *Navbar Cart Icons
- Mod Not Turned On* so it can't be forgotten.

---

## Troubleshooting

### The icons don't appear

- Is there something in the cart? With an empty cart nothing shows unless
  **Show The Cart Icon When The Cart Is Empty?** is `true`.
- Is **Enable Navbar Cart Icons?** set to `true`?
- Are you looking at a phone-sized window? On a desktop the navbar is expanded
  and the icons are hidden by design; its own *Shopping Cart* and *Checkout*
  links are in view.
- View the page source and search for `navbarCartIcons`. If it's there but
  invisible, it's a styling matter — most often the icons are the same color
  as the navbar background. Set **Icon Color**.
- If it isn't in the source, the plugin didn't find a ZCA Bootstrap navbar: a
  `<nav>` with the `navbar` class holding a `navbar-toggler` button. On
  responsive_classic or any other template the plugin does nothing. On a
  ZCA Bootstrap clone that reworked the header, see
  [CUSTOMIZING.md](CUSTOMIZING.md).

### The icons show as empty boxes

The Font Awesome class names in **Shopping Cart Icon Class** and **Checkout
Icon Class** don't exist in the Font Awesome version your template loads. The
defaults (`fas fa-shopping-cart`, `fas fa-credit-card`) are known to both
Font Awesome 5 and 6; if you changed them, check the name on
fontawesome.com for your version.

### The hamburger dropped onto a second line

**Keep Everything On One Line?** is `false`, or your navbar's first item isn't
the one taking the room. The one-line rule shrinks the navbar's first child
(a brand, a logo, the Trademark plugin's mark) so the icons and the hamburger
stay in the top row. If your template puts something else first, style it
yourself: see [CUSTOMIZING.md](CUSTOMIZING.md).

### The stylesheet 404s

View source and check this URL loads:

```
zc_plugins/NavbarCartIcons/v1.0.0/catalog/includes/templates/template_default/css/navbar_cart_icons.css
```

Zen Cart's shipped `zc_plugins/.htaccess` denies everything then explicitly
re-allows `.css`, `.js` and `.html`, so on a normal Apache host this works. If
your server ignores `.htaccess` — nginx, LiteSpeed without compatibility mode,
or Apache with `AllowOverride None` plus its own deny rule — you may need a
rule permitting those types under `zc_plugins/`. The same applies to the Read
Me link in Plugin Manager.

### After upgrading, the old styling is still showing

Browsers cache the stylesheet for up to an hour. A hard reload (Ctrl+F5) clears
it. Each plugin version lives in its own directory, so a new version is always a
new URL.

### The admin menu entry is missing after installing

Log out and back in. If it's still missing, uninstall and re-install — that
rewrites the `admin_pages` record.
