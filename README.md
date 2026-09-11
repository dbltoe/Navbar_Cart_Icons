# Navbar Cart Icons 1.0.0

An encapsulated Zen Cart plugin that puts a **Shopping Cart** icon and a
**Checkout** icon beside the hamburger button of a ZCA Bootstrap navbar, on
phones and tablets, whenever the cart has something in it. A customer no
longer has to open the menu to get to the cart or to checkout.

**Nothing in your template is changed.** Install it from Plugin Manager and
the icons are there; uninstall it and everything is back the way it was.

**Runs on Zen Cart v1.5.8 through v3.0.0 and PHP 7.4 through 8.5 from a single
codebase**, verified against all six release branches rather than assumed. See
[docs/COMPATIBILITY.md](docs/COMPATIBILITY.md).

---

## The story behind it

ZCA Bootstrap collapses its whole navigation into a hamburger on a phone. That
includes the *Shopping Cart* and *Checkout* links it adds once something is
in the cart, so the two things a buying customer most wants are two taps away
and out of sight. A client asked for the cart and checkout icons to sit next
to the hamburger instead, where every other shopping app puts them.

The first version was a hand edit of the template's `tpl_header.php`. It
worked, and it would have been lost the next time the template was updated.
So it became this plugin: the same icons, injected into the rendered page
with no template file touched, on any ZCA Bootstrap store.

## What it does

```
Phone, ZCA Bootstrap, something in the cart:

  Hare Do            [cart] [card] [≡]

Phone, empty cart:

  Hare Do                          [≡]

Desktop: nothing changes. The navbar is expanded and its own
Shopping Cart and Checkout links are in view.
```

- Works with **ZCA Bootstrap** (3.3.0 through 3.8.0) and clones of it, with
  nothing to configure. The icons follow the navbar's own `navbar-expand-*`
  breakpoint, so they come and go exactly when the hamburger does.
- The icons link to the same two pages ZCA Bootstrap's own menu entries do,
  built with the same calls, and carry the same labels. A store that has
  reworded or translated *Shopping Cart* and *Checkout* is followed.
- Each icon is a 44px touch target, with finger room between them and before
  the hamburger. Both are settings.
- **Keeps everything on one line.** A store name, logo or trademark ahead of
  the icons shrinks and clips with an ellipsis rather than pushing the
  hamburger onto a second line on a narrow phone or a large-text setting.
- Coexists with the Trademark plugin, and with a template that still carries
  a hand edit of the same thing: a page that already has `#navbarCartIcons`
  is left alone.
- Thirteen settings: which icons show, whether the cart icon shows on an empty
  cart, an optional item-count badge, the Font Awesome classes of both icons,
  the color, hover color, size and spacing. The defaults are right for most
  stores.
- One stylesheet, no JavaScript, no CDN, no tables of its own.

## Requirements

| | |
|---|---|
| Zen Cart | v1.5.8 or later, including v3.0.0-dev |
| PHP | 7.4 – 8.5 |
| Template | ZCA Bootstrap 3.3.0–3.8.0 or a clone of it. On any other template the plugin does nothing at all. |

## Installation

Copy `zc_plugins/NavbarCartIcons/` into the `zc_plugins/` directory of your
store, then install from **Admin → Modules → Plugin Manager**. Full
instructions are in [docs/INSTALL.md](docs/INSTALL.md) and in the
`readme.html` shipped with the plugin, which Plugin Manager links from its
info panel.

## Documentation

- [docs/INSTALL.md](docs/INSTALL.md) — installing, upgrading, uninstalling, troubleshooting
- [docs/CONFIGURATION.md](docs/CONFIGURATION.md) — every setting
- [docs/CUSTOMIZING.md](docs/CUSTOMIZING.md) — styling, the count badge, other icons
- [docs/COMPATIBILITY.md](docs/COMPATIBILITY.md) — how one codebase spans v1.5.8 → v3.0.0, and what it relies on
- [CHANGELOG.md](CHANGELOG.md)

## Support

Questions and bug reports go in the plugin's thread on the Zen Cart forum,
which Plugin Manager links from the plugin's info panel once it exists.

## Legal stuff

GNU General Public License v2.0, the same license Zen Cart itself is
distributed under. As always, no warranty or guarantee is applied or implied.

**ALWAYS MAKE BACKUPS BEFORE ADDING/EDITING ANY MOD.**
