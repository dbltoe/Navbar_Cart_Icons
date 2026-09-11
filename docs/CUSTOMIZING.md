# Customizing Navbar Cart Icons

---

## The markup

What the plugin inserts, immediately ahead of the navbar's toggler button, is
one Bootstrap nav list:

```html
<ul id="navbarCartIcons" class="navbar-nav flex-row ml-auto ms-auto d-lg-none">
  <li class="nav-item" title="Shopping Cart">
    <a class="nav-link" href="…shopping_cart" aria-label="Shopping Cart">
      <i class="fas fa-shopping-cart" aria-hidden="true"></i>
    </a>
  </li>
  <li class="nav-item" title="Checkout">
    <a class="nav-link" href="…checkout_shipping" aria-label="Checkout">
      <i class="fas fa-credit-card" aria-hidden="true"></i>
    </a>
  </li>
</ul>
```

(on one line in the page). With the count badge on, the cart link also holds
`<span class="nciCount" aria-hidden="true">3</span>` and its `aria-label`
becomes *Shopping Cart (3)*.

- `ml-auto` (and its Bootstrap 5 twin `ms-auto`) pushes the list to the right,
  against the toggler, whatever comes before it.
- `d-lg-none` hides it once the navbar expands. The `lg` is read from the
  navbar's own `navbar-expand-lg` class; a navbar that expands at `md` gets
  `d-md-none`, and so on.
- The `href`s are exactly what `zen_href_link(FILENAME_SHOPPING_CART)` and
  `zen_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')` return — the same
  calls ZCA Bootstrap's own header makes. A store with One Page Checkout gets
  its usual redirect from `checkout_shipping` to `checkout_one`.
- The labels are `HEADER_TITLE_CART_CONTENTS` and `HEADER_TITLE_CHECKOUT`, the
  same language constants the menu uses, so a store that has reworded or
  translated them is followed.

## Styling

Spacing, size and colors come from the settings and are written into the page
as one inline rule just before `</head>`:

```html
<link rel="stylesheet" href="/zc_plugins/NavbarCartIcons/v1.0.0/catalog/includes/templates/template_default/css/navbar_cart_icons.css">
…
<style id="navbarCartIconsStyle">#navbarCartIcons{margin-right:10px}#navbarCartIcons .nav-item+.nav-item{margin-left:10px}#navbarCartIcons .nav-link{font-size:1.5rem}@media (max-width:991.98px){#navMain nav.navbar>:first-child:not(.navbar-toggler):not(.navbar-collapse):not(#navbarCartIcons){flex:1 1 0;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}}</style>
```

Everything else is in the stylesheet:

```
zc_plugins/NavbarCartIcons/v1.0.0/catalog/includes/templates/template_default/css/navbar_cart_icons.css
```

It's short: each link is a 44px-square block with the glyph centered, and the
count badge is a small red circle in the link's top-right corner.

Three ways to change it, in increasing order of commitment:

### 1. Use the settings

Color, hover color, size and spacing cover most needs.

### 2. Add rules to your template's own CSS

Zen Cart loads every `style*.css` in your template's `css/` folder, but the
plugin's link goes into `<head>` *after* them (it's inserted just before
`</head>`), and the inline rule after that. A plain
`#navbarCartIcons .nav-link { … }` rule of yours ties with the inline rule on
specificity and loses on order. Raise the specificity and yours wins:

```css
#navMain #navbarCartIcons .nav-link { color: #9a044f; }
#navMain #navbarCartIcons .nciCount { background: #1a3b4d; }
```

### 3. Take over the stylesheet

Copy `navbar_cart_icons.css` into `includes/templates/YOUR_TEMPLATE/css/`. The
plugin checks there first and links that copy instead of its own, so the rules
are yours to change. Then, if you'd rather set colors and spacing in CSS too,
switch **Load The Plugin Stylesheet?** off: nothing of the plugin's is linked
and your template's CSS is all there is. (The one-line rule goes with it, so
add your own if you want it.)

---

## The one-line rule

The navbar is a wrapping flex container. Whatever your template puts ahead of
the icons — a `.navbar-brand`, a logo, the Trademark plugin's
`<p class="tradeMark">` — has a fixed width, and on a 320px phone, or with a
large-text setting that scales everything up, brand + icons + hamburger can
exceed the bar. Bootstrap then wraps the hamburger onto a second line, which
looks broken.

With **Keep Everything On One Line?** on, the plugin gives the navbar's first
child a zero flex basis and lets it clip:

```css
@media (max-width: 991.98px) {
  #navMain nav.navbar > :first-child:not(.navbar-toggler):not(.navbar-collapse):not(#navbarCartIcons) {
    flex: 1 1 0; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
  }
}
```

The brand takes whatever room is left and shows an ellipsis when that isn't
enough; the icons and the hamburger never move. It applies only while the
navbar is collapsed, so the desktop layout is untouched, and `#navMain` gives
it enough specificity to beat a plugin's or template's `nav.navbar .brand`
rule, whichever loads last.

If your brand is an image that mustn't be clipped, switch the setting off and
size the image with a `max-width` in `vw` instead, or hide its text part on
tiny screens. A store whose logo already carries the name can do this:

```css
@media (max-width: 359.98px) {
  #navMain nav.navbar:has(#navbarCartIcons) .tradeMark { font-size: 0; }
}
```

which keeps the logo and drops the text only while the icons are present.

---

## Other icons

Any Font Awesome icon your template's Font Awesome build includes. ZCA
Bootstrap 3.3–3.5 load Font Awesome 5 and 3.6+ load Font Awesome 6, and the
`fas fa-shopping-cart` / `fas fa-credit-card` names are in both. If you set a
6-only name (`fa-solid fa-cart-shopping`) on a Font Awesome 5 store you'll get
an empty box.

The classes are checked before they reach the page — letters, digits, spaces,
hyphens and underscores only — so nothing typed into those two settings can
break out of the attribute.

## The count badge

`nciCount` is positioned in the link's top-right corner and styled in the
stylesheet: red, white bold digits, half the icon's font size. The count is
whatever `count_contents()` reports, so fractional quantities show as such.
Override it from your template CSS with `#navMain #navbarCartIcons .nciCount`.

---

## A clone that reworked the header

The plugin recognizes a ZCA Bootstrap header by a `<nav>` carrying the
`navbar` class that holds a `<button class="navbar-toggler">`. It puts the
icons immediately ahead of that button, and reads the breakpoint from the
`navbar-expand-*` class on the `<nav>`. A clone that kept those works as-is;
one that dropped the toggler, or renamed the classes, gets nothing. There's no
custom-anchor setting in this version: the whole point is the hamburger, and a
navbar without one has nothing to sit beside.

If you'd like the plugin to recognize a particular header out of the box, open
an issue on GitHub with a copy of its rendered markup.
