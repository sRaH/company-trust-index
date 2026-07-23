# CompanyTrustIndex — Design System

This document describes the visual design system, layout architecture, and component
conventions for the CompanyTrustIndex frontend. It is the source of truth for anyone
adding a page or component: read the tokens, reuse the partials, match the layers.

---

## 1. Principles

1. **Trust + warmth.** The palette pairs a confident teal (trust) with warm paper tones
   and amber ratings — deliberately *not* stock Bootstrap indigo on grey.
2. **Intentional, not templated.** A display serif (Fraunces) for headings against a
   humanist sans (Hanken Grotesk) for body, opaque ink navbar, soft radii, layered shadows.
3. **Modular by default.** Pages compose reusable Twig partials; styles live in layered
   SCSS files; tokens drive both Bootstrap and custom components.
4. **Bootstrap as a base, not a ceiling.** Bootstrap variables are overridden at the token
   layer; custom components extend (never fight) Bootstrap classes.

---

## 2. Stack & asset pipeline

| Concern        | Choice                                                      |
|----------------|------------------------------------------------------------|
| Framework      | Symfony 7.4 + Twig 3                                       |
| CSS framework  | Bootstrap 5 (SCSS source, overridden by our tokens)        |
| Bundler        | Vite via `pentatrion/vite-bundle` + `vite-plugin-symfony`  |
| Stylesheet     | `assets/styles/app.scss` (single Vite entry `app`)         |
| JS/interactivity| Stimulus (`assets/controllers/`)                          |
| Fonts          | Fraunces + Hanken Grotesk via Google Fonts `<link>` in head|

Layer order is critical and enforced by `app.scss`:

```
variables (tokens + Bootstrap overrides)  →  bootstrap  →  layout  →  components  →  utilities
```

`_variables.scss` is imported **before** Bootstrap so Bootstrap's `!default` values resolve
to our tokens. The project layers come **after** so they can refine Bootstrap output.

```scss
// assets/styles/app.scss
@import "variables";
@import "bootstrap/scss/bootstrap";
@import "layout";
@import "components";
@import "utilities";
```

---

## 3. Design tokens

All tokens live in `assets/styles/_variables.scss`. They are plain SCSS variables (used by
Bootstrap overrides and our components) **and** mirrored as CSS custom properties on `:root`
(`--cti-*`) for runtime/inline reuse.

### 3.1 Brand palette

| Token            | Value      | Use                                  |
|------------------|------------|--------------------------------------|
| `$cti-ink`       | `#11202e`  | headings, navbar bg, primary text    |
| `$cti-ink-soft`  | `#1f2f3b`  | body text                            |
| `$cti-primary`   | `#0e7c7b`  | teal — brand / primary               |
| `$cti-primary-700`| `#0a5f5e` | teal hover / pressed, links          |
| `$cti-primary-050`| `#e8f2f1` | teal-tinted background               |
| `$cti-gold`      | `#e0a02f`  | amber — stars / rating warmth        |
| `$cti-gold-600`  | `#c2841c`  | amber hover                          |
| `$cti-gold-050`  | `#fbf2dd`  | amber-tinted background              |
| `$cti-paper`     | `#f6f4ee`  | page background (replaces stock #eee)|
| `$cti-surface`   | `#ffffff`  | card surface                         |
| `$cti-line`      | `#e4e0d6`  | warm hairline border                 |
| `$cti-muted`     | `#6a757c`  | muted secondary text                 |

Feedback colors follow the same logic (`$cti-success #2f7d52`, `$cti-danger #b3261e`,
`$cti-warning #b26a00`, each with a `*-bg` tint).

### 3.2 Typography

- **Display / headings:** `Fraunces` (variable opsz serif), weight 500–700, `$headings-font-weight: 600`.
- **Body:** `Hanken Grotesk` (humanist sans), 400/500/600/700.
- Headings inherit `$headings-color: $cti-ink`; body uses `$body-color: $cti-ink-soft`.
- Fonts are loaded with a Google Fonts `<link>` (with `preconnect`) in `base.html.twig`;
  the chosen weights cover Hungarian `ő`/`ű`.

### 3.3 Radii, shadows, focus rings

| Token           | Value                                                        |
|-----------------|--------------------------------------------------------------|
| `$cti-radius`   | `0.75rem` (12px) — default; `_sm` 0.5rem, `_lg` 1.125rem     |
| `$cti-shadow-sm`| `0 1px 2px rgba(17,32,46,.06)`                               |
| `$cti-shadow`   | `0 6px 18px rgba(17,32,46,.07), 0 1px 3px rgba(17,32,46,.05)`|
| `$cti-shadow-lg`| `0 18px 40px rgba(17,32,46,.12)`                             |
| `$cti-ring-primary` | `0 0 0 3px rgba($cti-primary,.28)` — focus ring          |
| `$cti-ring-gold`| `0 0 0 3px rgba($cti-gold,.30)`                              |

### 3.4 CSS custom properties

Every token above is also exposed as `--cti-*` on `:root` (e.g. `--cti-primary`,
`--cti-radius`, `--cti-shadow`, `--cti-font-display`). Use these in inline styles or any
context where a SCSS variable isn't reachable.

### 3.5 Bootstrap overrides (summary)

`$primary`, `$secondary`, `$dark`, `$success`, `$danger`, `$warning`; `$body-bg`,
`$body-color`, `$headings-color`, `$link-color`, `$border-color`; font families; border
radii; `$btn-focus-box-shadow`, `$input-focus-border-color`; navbar-dark color scale;
`$card-*` (warm border, transparent cap bg, 12px radius, 1.4/1.5rem padding); `$table-border-color`.

---

## 4. Layout architecture

### 4.1 `templates/base.html.twig`

The base template defines the page shell and named override blocks:

```twig
<head>
    {% block meta %} … {% block meta_description %}{% endblock %} … {% endblock %}
    {% block title %}{% endblock %}
    {# Google Fonts <link> #}
    {% block stylesheets %}{{ vite_entry_link_tags('app') }}{% endblock %}
    {% block javascripts %}{{ vite_entry_script_tags('app') }}{% endblock %}
</head>
<body>
    <a class="skip-link" href="#main-content">Ugrás a tartalomra</a>
    {% block header %}{% include 'components/_navbar.html.twig' %}{% endblock %}
    {% include 'components/_flashes.html.twig' %}
    <main class="page-main" id="main-content">
        <div class="container">{% block content %}{% endblock %}</div>
    </main>
    {% block footer %}{% include 'components/_footer.html.twig' %}{% endblock %}
</body>
```

**Block contract** (override these from page templates):

| Block              | Purpose                                       |
|--------------------|-----------------------------------------------|
| `title`            | `<title>` text                                |
| `meta_description` | `<meta name="description">` content           |
| `meta`             | entire `<head>` meta region (escape hatch)    |
| `stylesheets` / `javascripts` | per-page Vite entry additions       |
| `header` / `footer`| replace the navbar/footer (rare)             |
| `content`          | **the page body** — put page markup here      |

Page templates should `{% extends 'base.html.twig' %}` and only fill `title`,
`meta_description`, and `content`. They must **not** render their own navbar, footer, or
flash region — those are centralized.

### 4.2 Page shell & navbar

- `.page-main` has a min-height + flex on `body` so the footer is pushed to the bottom on
  short pages. A subtle paper grain is applied to the background so the warm tone never
  reads as a flat fill.
- **Navbar** (`.cti-navbar`): opaque ink (`#11202e`) using Bootstrap's `navbar-dark` scale,
  container-width, collapses below `lg`. Brand mark is an inline SVG (white + amber bars);
  brand wordmark is `Company` + gold `Trust` + `Index`. Nav items derive **active state from
  the current route** (`app.request.attributes.get('_route')`), not from hard-coded pages.
  The "Új vélemény írása" link is a `.nav-link--cta` (primary-tinted pill).
- **Footer** (`.cti-footer`): brand mark + Hungarian tagline + dynamic copyright year.

---

## 5. Component inventory

Reusable Twig partials live in `templates/components/`. Include them; do not copy markup.

| Partial                          | Purpose                                  | Variables                                                                 |
|----------------------------------|------------------------------------------|---------------------------------------------------------------------------|
| `_navbar.html.twig`              | Site navbar + brand + nav                | none (reads `app.request`)                                                |
| `_footer.html.twig`              | Site footer                              | none                                                                      |
| `_flashes.html.twig`             | Centralized flash region                 | none (reads `app.flashes`)                                                |
| `_star_rating.html.twig`         | **Read-only** star display (half-stars)  | `rating` (0–5, float ok), `value?` (numeric suffix), `large?` (bool)      |
| `_review_card.html.twig`         | One review row in a list                 | `review` (`App\Entity\Review`)                                            |
| `_empty_state.html.twig`         | Icon + message + optional CTA            | `message`, `cta_text?`, `cta_href?`                                       |

### 5.1 Star ratings — two distinct concepts (do not conflate)

1. **Display** — `components/_star_rating.html.twig` → `.star-rating-display`.
   Read-only, supports **fractional values** via two stacked glyph layers inside a
   `__track`: an empty base + a gold fill clipped to `width = rating/5 * 100%`. So a 4.3
   average renders 4 full stars + a ~30% partial fifth. Use for the homepage list, the
   companies stats table, and the review detail header (`large` variant).

2. **Interactive input** — lives inline in `review/new.html.twig` → `.star-rating-widget`.
   Backed by `assets/controllers/star_rating_controller.js`. **This has its own DOM
   contract that must not change** (see §6).

### 5.2 Cards

| Class             | Use                                                        |
|-------------------|------------------------------------------------------------|
| `.card`           | default surface                                           |
| `.card--surface`  | lifted primary content block (forms, detail, list head)   |
| `.card--accent`   | detail card with a quiet teal top accent line (review show)|

### 5.3 Buttons

Bootstrap classes, refined by the token layer: `.btn-primary` (teal, warm hover ring),
`.btn-outline-secondary` (reads on warm paper), plus a custom `.btn-ghost-ink`
(ghost-on-dark / navbar contexts).

### 5.4 Other patterns

- **Rating badge** (`.rating-badge`) wraps a rating next to the display stars on the review
  detail header.
- **Section heads**: `.section-head` + `.eyebrow` (small uppercase teal label) for page
  titles and section rhythm.
- **Flashes** render as Bootstrap dismissible alerts with a soft entrance animation,
  inside `role="status" aria-live="polite"`. Supported types: `success`, `error`,
  `warning`, `danger`.

---

## 6. Stimulus DOM contracts (must not break)

Two interactive widgets depend on exact classes/attributes. You may restyle spacing/color;
**do not** change structure or remove hooks.

### Star rating input (`star_rating_controller.js`)
Container `data-controller="star-rating"`; star targets
`data-star-rating-target="star"` + `data-star-value="N"`; hidden input
`data-star-rating-target="input"`; classes `.star-active` / `.star-inactive`; widget
`.star-rating-widget`, star `.star-rating-star`. Only used in `review/new.html.twig`.

### Company autocomplete (`company_autocomplete_controller.js`)
Dropdown `.company-autocomplete-results`; the input/`data-action`/value wiring lives in the
`company_autocomplete_widget` block in `templates/form/fields.html.twig`. Restyle the
dropdown via `.company-autocomplete-results` tokens; do not alter its DOM.

---

## 7. Accessibility

- Skip link "Ugrás a tartalomra" → `#main-content`, visually hidden until focused.
- `aria-label`s on navbar, footer, flash region (`role="status"`, `aria-live="polite"`).
- Star display uses `role="img"` + `aria-label="Értékelés: X / 5"`.
- Visible focus rings via `$cti-ring-primary` / `$cti-ring-gold`.
- Palette chosen for adequate contrast on warm paper and the ink navbar.

---

## 8. Conventions

### Adding a page
1. Create `templates/<section>/<page>.html.twig` extending `base.html.twig`.
2. Fill `title`, `meta_description`, and `content`. Use `.section-head` + `.eyebrow` for
   the page title; reuse existing partials (`_review_card`, `_star_rating`,
   `_empty_state`) before writing new markup.
3. Do not add a navbar, footer, or flash block — they're centralized.

### Adding a component
1. Extract repeated markup into `templates/components/_<name>.html.twig`; pass data via
   `include … with { … } only`.
2. Style it as a scoped block in `assets/styles/_components.scss` using `$cti-*` tokens
   (not raw hex). Expose any new token in `_variables.scss` (both the SCSS var and the
   `--cti-*` custom property).
3. Keep class naming BEM-ish (`block`, `block__element`, `block--modifier`) to match the
   existing components.

### Token changes
Edit `_variables.scss`. Because Bootstrap reads our overrides via `!default`, changing a
token cascades to every Bootstrap component automatically. Re-run `npm run build`.

---

## 9. Commands

```bash
npm run build            # compile SCSS/JS → public/build (production manifest)
npm run dev              # Vite dev server (HMR), run alongside the app server
php bin/console lint:twig templates -n
make lint                # phpstan + cs-check + twig/container lint + schema validate
make build               # alias: npm run build
```

Page QA: load fixtures for realistic content, then build and serve:

```bash
php bin/console doctrine:fixtures:load -n -e dev
make build
php -S 127.0.0.1:8123 -t public
```
