=== Axellcore — Atelier Club ===
Contributors: axell
Tags: axell, atelier, landing-page, blocks
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.1.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Self-contained landing page (FSE template + core blocks + a custom application-form block) for the Atelier Axell Club invite program.

[Preview the latest release in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/axellhydrosystems/axellcore-atelierclub/main/blueprint.json) · [Preview the latest `main` build](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/axellhydrosystems/axellcore-atelierclub/main/blueprint-dev.json)

== Description ==

Ships the Atelier Axell Club landing page as a plugin-owned FSE template (registered via `register_block_template()`, editable in the Site Editor, no theme header/footer) plus two small custom blocks (`axellcore/form`, `axellcore/form-input`) for the application form — every other section is composed from core WordPress blocks styled with the plugin's own `aac-`-prefixed stylesheet. On activation, the plugin provisions the `/atelier-club` page automatically if it doesn't already exist.

== Changelog ==

= 0.1.1 =
* Replaced almost all Custom HTML blocks in the seeded content with real core-block composition (Group/Columns/Paragraph/Heading/List/Buttons) — nav, hero, "A Placa" visual, tier lock-marks, benefit prize chips, editorial rows/cards, CTA strip, and the footer are now genuinely WYSIWYG-editable, verified live in the block editor (no "invalid block" warnings). Only 4 tiny, structurally-justified Custom HTML blocks remain (3 purely-decorative empty layers, plus the 5 partner-store inputs).
* Registers a small icon set via WordPress 7.1's Icons API (`wp_register_icon_collection()`/`wp_register_icon()`) and renders the lock/clock glyphs with the native `core/icon` block instead of inline SVG.
* The form's submit control is now a real `core/button` with `tagName:"button"`/`type:"submit"` (confirmed via the block editor's Code editor round-trip) — native form-submit semantics instead of an `<a>` styled as a button.
* CTA arrow icons moved from inline SVG to a CSS `mask-image` pseudo-element on `.aac-btn`/`.aac-tier-cta` (see assets/css/blocks-bridge.css), so `core/button` needs only its "Additional CSS class(es)" field, no per-instance markup.

= 0.1.0 =
* FSE blank-canvas template registration, theme/core asset suppression, design tokens + section CSS ported from the approved static mockup, shared frontend behavior (nav scroll state, scroll-reveal, CPF/CNPJ/CEP/phone input masks), and the `axellcore/form` / `axellcore/form-input` blocks.
* Full landing-page content (all 14 sections + the Adesão application form) authored as core-block markup and seeded automatically on activation — pixel-matched against the approved mockup and verified in-browser.
* Fix: bypass KSES for the plugin's own trusted seed content on activation — WP-CLI/no-user contexts were silently stripping `<select>`/`<input>` tags from the form on insert.
* pt_BR translation (46/46 strings) alongside the English source.
