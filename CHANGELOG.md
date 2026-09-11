# Changelog

All notable changes to this project are recorded here. This project follows
[Semantic Versioning](https://semver.org/).

## [1.0.0] — 2026-09-11

First release.

Puts a Shopping Cart icon and a Checkout icon beside the hamburger button of
a ZCA Bootstrap navbar, on the screen sizes where the navbar is collapsed,
whenever the cart has something in it. Install it from Plugin Manager and
they're there; nothing in your template is edited, copied or overridden, and
uninstalling puts everything back.

### Added

- One codebase for Zen Cart v1.5.8 through v3.0.0 and PHP 7.4 through 8.5,
  verified against all six release branches.
- Works with ZCA Bootstrap (3.3.0 through 3.8.0) and with clones of it, with
  nothing to configure. The icons follow the navbar's own `navbar-expand-*`
  breakpoint, so a template that expands its navbar at a different size still
  gets them exactly when it shows its hamburger.
- The icons link to the same two pages ZCA Bootstrap's own menu entries do,
  built with the same calls, and carry the same labels, so a store that has
  reworded or translated *Shopping Cart* and *Checkout* is followed.
- Each icon is a 44px touch target with finger room between them and before
  the hamburger; both are settings.
- **Keep Everything On One Line?** A store name, logo or trademark ahead of
  the icons shrinks and clips with an ellipsis rather than pushing the
  hamburger onto a second line on a narrow phone or a large-text setting.
- Settings for which icons show, whether the cart icon shows on an empty cart,
  an optional item-count badge, the Font Awesome classes of both icons, the
  color, hover color, size and spacing.
- The stylesheet can be overridden from your template (a
  `navbar_cart_icons.css` in its `css/` directory is used instead) or switched
  off entirely if you'd rather style `#navbarCartIcons` yourself.
- A page that already carries `id="navbarCartIcons"` — a template holding a
  hand edit of the same thing — is left alone, so you can install first and
  remove the edit when convenient.
- Self-contained `readme.html` shipped with the plugin and linked, with the
  GitHub project page, as buttons in the Plugin Manager info panel.
- Idempotent installer. Settings you've changed are never reset by an upgrade;
  labels and help text are refreshed to the current version.
- While the plugin is switched off, Plugin Manager lists it as *Navbar Cart
  Icons - Mod Not Turned On*, on every supported release.

[1.0.0]: https://github.com/dbltoe/Navbar_Cart_Icons/releases/tag/v1.0.0
