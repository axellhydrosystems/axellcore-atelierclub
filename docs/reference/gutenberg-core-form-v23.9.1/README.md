# Reference: Gutenberg's experimental `core/form` block family (v23.9.1)

Vendored, read-only copy of the four experimental form blocks from the
Gutenberg plugin, for comparison against this plugin's `axellcore/form*`
blocks. **Never loaded at runtime** — not required/enqueued anywhere in this
plugin. Kept here purely so a future session doesn't have to re-clone
Gutenberg to look at this code again.

## Origin

- Source: [`WordPress/gutenberg`](https://github.com/WordPress/gutenberg),
  tag `v23.9.1`, path `packages/block-library/src/form{,-input,-submit-button,-submission-notification}`.
- Fetched via: `git clone --filter=blob:none --no-checkout --depth=1 --branch v23.9.1 https://github.com/WordPress/gutenberg.git`
  + `git sparse-checkout` on those four directories.
- License: GPL-2.0-or-later — same license as this plugin (see
  `../../readme.txt`), so vendoring this trimmed subset is compatible.

## What these blocks were

`core/form`, `core/form-input`, `core/form-submit-button`, and
`core/form-submission-notification` — all marked `"__experimental": true` in
their `block.json` and gated behind the `gutenberg-form-blocks` experiment
flag (`register_block_core_form()` etc. in each block's `index.php` bail out
via `gutenberg_is_experiment_enabled( 'gutenberg-form-blocks' )` unless a user
had explicitly enabled it under Gutenberg → Experiments). They existed in this
form through the Gutenberg plugin's `v23.9.1` release.

## Removal

Removed in Gutenberg **24.0.0** by
[PR #82451 — "Block Library: Remove the form blocks experiment"](https://github.com/WordPress/gutenberg/pull/82451)
(merged 2026-09-04 into `trunk`). From the PR description:

> These blocks have lived behind the `gutenberg-form-blocks` experiment since
> they were introduced and are no longer being pursued. Keeping them around
> means maintaining four block directories, a kses filter that widens the
> allowed HTML for every site with the experiment on, an AJAX email handler,
> and a set of fixtures for blocks nobody can use without opting in. Removing
> the experiment cleans all of that out.

The specific commit originally cited when this reference was pulled:
<https://github.com/WordPress/gutenberg/pull/82451/changes/7031103625af628f06caa5b6a87065f103de12cd>.

## Relationship to `axellcore/form*`

`axellcore/form` + `axellcore/form-input` (and the blocks that came after,
see the plugin's own `CLAUDE.md`) are an **independent, from-scratch
implementation** — not a fork, not a dependency. The removal of this
experiment from Gutenberg core has **zero effect** on this plugin; it was
never installed, imported, or required. This folder exists only because the
design was informed by these blocks' ergonomics (Inspector layout, per-type
inserter variations, the hidden-field editor placeholder, the notification
block pattern), and it's useful to have the real source on hand when deciding
what to port versus deliberately diverge from. See the plugin's own
`CLAUDE.md` for the current list of what was ported, adapted, or intentionally
skipped.
