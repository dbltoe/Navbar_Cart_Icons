# Configuration reference

Everything lives under **Admin → Configuration → Navbar Cart Icons**. There are
thirteen settings, and the defaults are right for most stores.

Setting titles are Title Case and end in a colon, or a question mark where the
setting is a yes/no question.

---

## The switch

| Setting | Default | What it does |
|---|---|---|
| **Enable Navbar Cart Icons?** | `true` | Master switch. `false` hides the icons on every page without uninstalling. While it's off, Plugin Manager lists the plugin as *Navbar Cart Icons - Mod Not Turned On*. **On from the start**, deliberately: with every other setting at its default the plugin shows the two icons whenever the cart has something in it, which is exactly what it's for. |

## What is shown

| Setting | Default | What it does |
|---|---|---|
| **Show The Shopping Cart Icon?** | `true` | The first icon, linking to the shopping cart page — the same page the menu's *Shopping Cart* entry goes to. |
| **Show The Checkout Icon?** | `true` | The second icon, linking straight into checkout — the same page the menu's *Checkout* entry goes to. Only ever shown while the cart has something in it. |
| **Show The Cart Icon When The Cart Is Empty?** | `false` | `false` matches ZCA Bootstrap's own menu, which lists Shopping Cart and Checkout only once there's something in the cart, so a visitor who hasn't added anything sees nothing extra. `true` shows the cart icon on every page regardless; the checkout icon still waits for an item. |
| **Show The Item Count On The Cart Icon?** | `false` | `true` adds a small badge to the cart icon with the number of items in the cart, as the cart itself counts them. The count is also added to the icon's accessible name, so a screen reader hears "Shopping Cart (3)". |
| **Shopping Cart Icon Class:** | `fas fa-shopping-cart` | The Font Awesome classes for the cart icon. ZCA Bootstrap loads Font Awesome on every page, so any of its icons works: `fas fa-shopping-basket`, `fas fa-shopping-bag`. Letters, digits, spaces and hyphens only; anything else is ignored and the default used. |
| **Checkout Icon Class:** | `fas fa-credit-card` | The Font Awesome classes for the checkout icon: `fas fa-cash-register`, `fas fa-money-check`. Same rules. |

The defaults use the `fas fa-...` names that both Font Awesome 5 and 6 know,
so they work on every ZCA Bootstrap release from 3.3.0 up.

## How it looks

| Setting | Default | What it does |
|---|---|---|
| **Keep Everything On One Line?** | `true` | A store name, logo or trademark ahead of the icons has a fixed width, and on a narrow phone, or with a large-text setting, it can push the icons and the hamburger down onto a second line. `true` lets that first item shrink and clip with an ellipsis instead, so the icons and the hamburger always stay in the top row. `false` leaves the navbar to wrap as it likes. Applies only while the navbar is collapsed; the desktop layout is never touched. |
| **Load The Plugin Stylesheet?** | `true` | Links the plugin's small stylesheet and applies the four settings below. Set to `false` if you'd rather style `#navbarCartIcons` entirely from your own template CSS; nothing of the plugin's then reaches the page but the icons themselves. |
| **Icon Color:** | *(empty)* | Any CSS color: `#9a044f`, `white`, `rgb(255,255,255)`, `var(--nav-fg)`. Empty means the same color as the navbar's other links. A value that doesn't look like a color is ignored — this is a setting that reaches the page as code rather than text, so it's checked. |
| **Icon Hover Color:** | *(empty)* | The color while a pointer is over an icon or it has keyboard focus. Empty means the navbar's own hover color. |
| **Icon Size:** | `1.5rem` | Any CSS size: `1.5rem`, `1.25em`, `24px`. The touch target around each icon stays at least 44px square whatever the size. |
| **Space Between The Icons:** | `10px` | The gap between the two icons, and between the last icon and the hamburger. Any CSS length. 10px is enough finger room to keep a tap from landing on the neighbor, which is what Apple's and Google's guidelines ask for. |

### Contrast

The icons take the navbar's link color by default, which your template has
already chosen against its bar color. If you set **Icon Color**, pick one that
meets [accessibility contrast standards](https://docs.zen-cart.com/user/accessibility/accessibility/#what-is-accessibility)
against the bar: an icon nobody can see isn't much of a shortcut.

---

## Where the values live

All thirteen are ordinary rows in Zen Cart's `configuration` table, in a group
titled **Navbar Cart Icons**, and become constants named `NAVBAR_CART_ICONS_*`
during `application_top`. Nothing is cached anywhere else, so a change on the
admin page is live on the next storefront request.

None of the rows carries a `val_function`; every value goes through Zen Cart's
normal admin sanitizer, and the plugin validates the four that reach the page
as CSS or as a class attribute on its own before printing them.
