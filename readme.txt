=== Axellcore — Atelier Club ===
Contributors: axell
Tags: axell, atelier, landing-page, blocks
Requires at least: 6.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Self-contained landing page (FSE template + core blocks + a custom application-form block) for the Atelier Axell Club invite program.

[Preview the latest release in WordPress Playground](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/axellhydrosystems/axellcore-atelierclub/main/blueprint.json) · [Preview the latest `main` build](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/axellhydrosystems/axellcore-atelierclub/main/blueprint-dev.json)

== Description ==

Ships the Atelier Axell Club landing page as a plugin-owned FSE template (registered via `register_block_template()`, editable in the Site Editor, no theme header/footer) plus two small custom blocks (`axellcore/form`, `axellcore/form-input`) for the application form — every other section is composed from core WordPress blocks styled with the plugin's own `aac-`-prefixed stylesheet. On activation, the plugin provisions the `/atelier-club` page automatically if it doesn't already exist.

== Changelog ==

= 0.0.1 =
* FSE blank-canvas template registration, theme/core asset suppression, design tokens + section CSS ported from the approved static mockup, shared frontend behavior (nav scroll state, scroll-reveal, CPF/CNPJ/CEP/phone input masks), and the `axellcore/form` / `axellcore/form-input` blocks.
* Full landing-page content (all 14 sections + the Adesão application form) authored as core-block markup and seeded automatically on activation — pixel-matched against the approved mockup and verified in-browser.
* Fix: bypass KSES for the plugin's own trusted seed content on activation — WP-CLI/no-user contexts were silently stripping `<select>`/`<input>` tags from the form on insert.
* pt_BR translation (46/46 strings) alongside the English source.
